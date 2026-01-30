<div class="w-full max-w-full md:w-[500px] h-[60vh] md:h-[70vh] min-h-[300px] flex flex-col bg-white dark:bg-gray-900 rounded-xl shadow-xl mt-4 md:mt-0 mb-4 ml-0.5"
    wire:ignore.self>
    @php
$area = Auth::user()?->area ?? '';
$isOtt = $area === 'OTT';
$isDth = $area === 'DTH';
$currentUserBubble = $isOtt ? 'bg-primary-600 text-white rounded-br-none' : ($isDth ? 'bg-secondary-600 text-white rounded-br-none' : 'bg-primary-600 text-white rounded-br-none');
$otherBubble = $isOtt ? 'bg-primary-100 dark:bg-primary-800 text-gray-900 dark:text-gray-100 rounded-bl-none' : ($isDth ? 'bg-secondary-100 dark:bg-secondary-800 text-gray-900 dark:text-gray-100 rounded-bl-none' : 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-bl-none');
$focusRing = $isOtt ? 'focus:ring-primary-500' : ($isDth ? 'focus:ring-secondary-500' : 'focus:ring-primary-500');
$btnPrimary = $isOtt ? 'bg-primary-600 hover:bg-primary-700' : ($isDth ? 'bg-secondary-600 hover:bg-secondary-700' : 'bg-primary-600 hover:bg-primary-700');
$iconAccent = $isOtt ? 'text-primary-600' : ($isDth ? 'text-secondary-600' : 'text-primary-600');
    @endphp
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-start justify-center items-center gap-2">
        <i class="fa-regular fa-comments text-gray-800 dark:text-gray-100 text-2xl"></i>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white leading-tight">{{ __('Comments') }}</h3>
    </div>

    <div id="chat-container" class="flex-1 overflow-y-auto px-2 py-2 space-y-2 flex flex-col min-h-[150px] h-full"
        style="scroll-behavior: smooth;">
        @forelse ($comments as $comment)
            @php
    $isCurrentUser = $comment->user_id === auth()->id();
            @endphp
            <div class="flex {{ $isCurrentUser ? 'justify-end' : 'justify-start' }}">
                <div class="flex items-end gap-2 max-w-[90%]">
                    @unless($isCurrentUser)
                        <img src="{{ $comment->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name ?? 'U') }}"
                            class="w-8 h-8 rounded-full border border-gray-300" alt="avatar">
                    @endunless
                    <div class="relative group">
                        <div class="p-3 rounded-xl shadow-sm transition-all duration-150 {{ $isCurrentUser ? $currentUserBubble : $otherBubble }}">
                            <div class="flex items-center gap-1 mb-1">
                                <span
                                    class="text-[11px] font-semibold opacity-70 {{ $isCurrentUser ? 'text-right' : 'text-left' }}">
                                    {{ $comment->user->name ?? __('Unknown') }}
                                </span>
                                <span class="text-[10px] opacity-50">
                                    · {{ $comment->created_at->diffForHumans() }}
                                </span>
                            </div>
                            @if($editingId === $comment->id)
                                <form wire:submit.prevent="updateComment({{ $comment->id }})" class="flex items-center gap-1">
                                    <input type="text" wire:model.defer="editBody" class="flex-1 px-2 py-1 rounded-md border
                                        border-gray-300 dark:border-gray-600
                                        bg-white dark:bg-gray-700
                                        text-gray-800 dark:text-gray-200
                                        placeholder-gray-400 dark:placeholder-gray-500
                                        focus:outline-none focus:ring-1 {{ $focusRing }}
                                        text-xs shadow-sm transition-colors duration-200" placeholder="{{ __('Edit your comment...') }}" />
                                    <button type="submit"
                                        class="px-1.5 py-0.5 text-[10px] rounded bg-green-500 hover:bg-green-600 text-white">
                                        ✔
                                    </button>
                                    <button type="button" wire:click="cancelEdit"
                                        class="px-1.5 py-0.5 text-[10px] rounded bg-red-500 hover:bg-red-600 text-white">
                                        ✖
                                    </button>
                                </form>
                            @else
                                <div class="text-sm break-words leading-snug">
                                    {{ $comment->body }}
                                </div>
                            @endif
                        </div>
                        @if($isCurrentUser)
                            <div class="absolute -top-2 right-0 flex gap-1 opacity-0 group-hover:opacity-100 transition">
                                <button wire:click="editComment({{ $comment->id }})"
                                    class="text-xs text-blue-400 hover:text-blue-600" title="Editar">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </button>
                                <button wire:click="deleteComment({{ $comment->id }})"
                                    class="text-xs text-red-400 hover:text-red-600" title="Eliminar">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                    @if($isCurrentUser)
                        <img src="{{ $comment->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name ?? 'U') }}"
                            class="w-8 h-8 rounded-full border border-gray-300" alt="avatar">
                    @endif
                </div>
            </div>
        @empty
            <div class="text-gray-400 text-center mt-2">{{ __('No comments yet.') }}</div>
        @endforelse
    </div>

    <div class="rounded-b-xl overflow-hidden">
        <form wire:submit.prevent="addComment"
            class="flex items-center gap-2 p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-b-xl">
            <input type="text" wire:model.defer="body"
                class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:outline-none focus:ring-2 {{ $focusRing }} text-sm shadow-sm"
                placeholder="{{ __('Add a comment...') }}">
            <button type="submit"
                class="{{ $btnPrimary }} text-white px-3 py-2 rounded-lg text-sm font-semibold shadow transition-all duration-200">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>
        @error('body')
            <div class="text-red-500 text-xs px-4 pb-2 bg-white dark:bg-gray-900 w-full">{{ $message }}</div>
        @enderror
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const chatContainer = document.getElementById("chat-container");
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
            Livewire.hook('message.processed', (message, component) => {
                const chat = document.getElementById("chat-container");
                if (chat) {
                    chat.scrollTop = chat.scrollHeight;
                }
            });

            window.Echo && window.Echo.private('report.{{ $reportId }}')
                .listen('CommentAdded', (e) => {
                    Livewire.dispatch('refreshComments');
                });
        });
    </script>
@endpush
