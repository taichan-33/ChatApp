<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- ヘッダー --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        管理画面
                    </h2>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ログアウト
                        </button>
                    </form>
                </div>
            </div>


            <div class="space-y-6">
                {{-- 設定更新フォーム --}}
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <h3 class="text-lg font-medium text-gray-900">各種設定</h3>
                        <p class="mt-1 text-sm text-gray-600">アプリケーション全体の設定をここで行います。</p>
                        
                        @if (session('success'))
                            <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="post" action="{{ route('admin.settings.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
                            @csrf

                            {{-- AI設定 --}}
                            <div>
                                <x-input-label for="system_prompt" :value="__('システムプロンプト')" />
                                <textarea id="system_prompt" name="system_prompt" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="5" required>{{ old('system_prompt', $settings->system_prompt ?? '') }}</textarea>
                            </div>
                            <div>
                                <x-input-label for="ai_model" :value="__('AIモデル名')" />
                                <x-text-input id="ai_model" name="ai_model" type="text" class="mt-1 block w-full" :value="old('ai_model', $settings->ai_model ?? '')" required />
                            </div>
                             <div>
                                <x-input-label for="openai_api_key" :value="__('OpenAI API Key')" />
                                <x-text-input id="openai_api_key" name="openai_api_key" type="password" class="mt-1 block w-full" :value="old('openai_api_key', $settings->openai_api_key ?? '')" />
                                <p class="mt-1 text-sm text-gray-500">APIキーは保存されていますが、セキュリティのため表示されません。変更する場合のみ入力してください。</p>
                            </div>

                            {{-- Bot設定 --}}
                            <div>
                                <x-input-label for="bot_name" :value="__('Bot名')" />
                                <x-text-input id="bot_name" name="bot_name" type="text" class="mt-1 block w-full" :value="old('bot_name', $settings->bot_name ?? '')" required />
                            </div>
                            <div>
                                <x-input-label for="bot_icon" :value="__('Botアイコン')" />
                                @if(!empty($settings->bot_icon_path))
                                    <img src="{{ Storage::url($settings->bot_icon_path) }}" alt="current bot icon" class="w-20 h-20 rounded-full object-cover my-2">
                                @endif
                                <input id="bot_icon" name="bot_icon" type="file" class="mt-1 block w-full" />
                                <p class="mt-1 text-sm text-gray-500">新しいアイコンをアップロードすると上書きされます。</p>
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('保存') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ▼▼▼ 「チャットへ戻る」ボタンをここに追加 ▼▼▼ --}}
            </h2>
                    <a href="{{ route('chat.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        チャットに戻る
                    </a>
            {{-- ▲▲▲ ここまで ▲▲▲ --}}

                {{-- ユーザー管理 --}}
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">ユーザー管理</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">名前</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">登録日</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @isset($users)
                                    @foreach ($users as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->created_at->format('Y/m/d H:i') }}</td>
                                    </tr>
                                    @endforeach
                                @endisset
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-4">
                        @isset($users)
                            {{ $users->links() }}
                        @endisset
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>