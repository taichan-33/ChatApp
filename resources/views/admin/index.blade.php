<x-app-layout>
    {{-- ▼▼▼ <x-slot name="header"> を削除し、新しいヘッダーを追加 ▼▼▼ --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        管理画面
                    </h2>
                    <a href="{{ route('chat.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        チャットに戻る
                    </a>
                </div>
            </div>
            {{-- ▲▲▲ ここまでが新しいヘッダー部分 ▲▲▲ --}}

            <div class="space-y-6">
                {{-- 設定更新フォーム --}}
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <h3 class="text-lg font-medium text-gray-900">AI設定</h3>
                        <p class="mt-1 text-sm text-gray-600">AIに渡すシステムプロンプトと、使用するモデルを設定します。</p>
                        
                        @if (session('success'))
                            <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="post" action="{{ route('admin.settings.update') }}" class="mt-6 space-y-6">
                            @csrf
                            <div>
                                <x-input-label for="system_prompt" :value="__('システムプロンプト')" />
                                <textarea id="system_prompt" name="system_prompt" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="5" required>{{ old('system_prompt', $settings->system_prompt) }}</textarea>
                            </div>
                            <div>
                                <x-input-label for="ai_model" :value="__('AIモデル名')" />
                                <x-text-input id="ai_model" name="ai_model" type="text" class="mt-1 block w-full" :value="old('ai_model', $settings->ai_model)" required />
                            </div>
                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('保存') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>

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
                                @foreach ($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->created_at->format('Y/m/d H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
