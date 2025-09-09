<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageFile;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function index()
    {
        $conversations = Auth::user()->conversations()->orderBy('updated_at', 'desc')->get();
        $latestConversation = $conversations->first();
        return view('chat.index', [
            'conversations' => $conversations,
            'currentConversation' => $latestConversation
        ]);
    }

    public function show(Conversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }
        $conversations = Auth::user()->conversations()->orderBy('updated_at', 'desc')->get();
        return view('chat.index', [
            'conversations' => $conversations,
            'currentConversation' => $conversation
        ]);
    }

    public function startNewConversation()
    {
        $conversation = Auth::user()->conversations()->create(['title' => '新しいチャット']);
        return redirect()->route('chat.show', $conversation);
    }

    public function chat(Request $request, Conversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) { abort(403); }
        
        // --- ログ出力追加 ---
        Log::debug('--- Chat Request Start ---');
        Log::debug('Conversation ID: ' . $conversation->id);

        $request->validate([
            'question' => 'nullable|string|max:14000',
            'files' => 'array|max:3',
            'files.*' => 'file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx|max:128000'
        ]);

        $userQuestion = $request->input('question', '');
        $userFiles = $request->file('files', []);
        
        // --- ログ出力追加 ---
        Log::debug('User Input: ', ['question' => $userQuestion, 'files' => count($userFiles)]);

        $userMessage = $conversation->messages()->create(['role' => 'user', 'content' => $userQuestion]);

        $filesForDb = [];
        foreach ($userFiles as $file) {
            $path = $file->store('chat_files', 'public');
            $filesForDb[] = [
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
            ];
        }

        if (!empty($filesForDb)) {
            $userMessage->files()->createMany($filesForDb);
        }

        if (($conversation->messages()->count() === 1) && ($conversation->title === '新しいチャット')) {
            $conversation->update(['title' => mb_substr($userQuestion, 0, 20) ?: 'ファイルチャット']);
        }

        $apiKey = config('app.openai_api_key');
        $settings = Setting::first();
        
        if (!$settings) {
            Log::error('AI settings not found in the database.');
            return response()->json([
                'success' => false, 
                'message' => 'AI設定がデータベースに見つかりません。管理者に連絡して、データベースシーダーを実行してもらってください。'
            ], 500);
        }
        
        // --- ログ出力追加 ---
        // Log::debug('AI Settings: ', ['model' => $settings->ai_model, 'system_prompt' => $settings->system_prompt]);
        
        $history = $this->buildHistoryPayload($conversation, $settings);
        
        // --- ログ出力追加 ---
        // Log::debug('Payload for OpenAI API: ', $history);

        $aiResponseContent = 'エラーが発生しました。';
        $isSuccess = false;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(600)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $settings->ai_model,
                'messages' => $history,
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                // --- ログ出力追加 ---
                // Log::debug('OpenAI API Success Response: ', ['content' => $content]);
                if ($content) {
                    $aiResponseContent = $content;
                    $conversation->messages()->create(['role' => 'assistant', 'content' => $aiResponseContent]);
                    $isSuccess = true;
                }
            } else {
                Log::error('OpenAI API Error: ' . $response->body());
                $aiResponseContent = 'APIからの応答エラーです。';
            }
        } catch (\Exception $e) {
            Log::error('HTTP Request Error: ' . $e->getMessage());
            $aiResponseContent = '通信エラーが発生しました。';
        }
        
        // --- ログ出力追加 ---
        // Log::debug('--- Chat Request End ---');

        return response()->json([
            'success' => $isSuccess,
            'message' => $aiResponseContent
        ]);
    }
    
    private function buildHistoryPayload(Conversation $conversation, Setting $settings): array
    {
        $history = [['role' => 'system', 'content' => $settings->system_prompt]];
        $messages = $conversation->messages()->with('files')->orderBy('created_at', 'asc')->get();

        foreach ($messages as $message) {
            if ($message->role === 'assistant') {
                $history[] = ['role' => 'assistant', 'content' => $message->content];
                continue;
            }

            $content = [];
            $textContent = $message->content;

            $nonImageFiles = $message->files->filter(fn($file) => !str_starts_with($file->mime_type, 'image/'));
            if ($nonImageFiles->isNotEmpty()) {
                $fileNames = $nonImageFiles->pluck('original_name')->implode(', ');
                $textContent .= "\n\n(添付ファイル: " . $fileNames . " - このファイルの内容は読み取れませんが、存在は認識してください)";
            }

            if (!empty(trim($textContent))) {
                $content[] = ['type' => 'text', 'text' => trim($textContent)];
            }

            foreach ($message->files as $file) {
                if (str_starts_with($file->mime_type, 'image/')) {
                    $content[] = [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => 'data:'.$file->mime_type.';base64,' . base64_encode(Storage::disk('public')->get($file->file_path))
                        ]
                    ];
                }
            }

            if(!empty($content)){
                 $history[] = ['role' => 'user', 'content' => $content];
            }
        }
        return $history;
    }

    public static function getFileIconClass(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) return 'bi-file-earmark-image';
        if (str_contains($mimeType, 'pdf')) return 'bi-file-earmark-pdf';
        if (str_contains($mimeType, 'word')) return 'bi-file-earmark-word';
        if (str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet')) return 'bi-file-earmark-excel';
        return 'bi-file-earmark-text';
    }
}

