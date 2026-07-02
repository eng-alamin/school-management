<div class="aicb-wrapper">

    {{-- Floating toggle button --}}
    <button type="button" class="aicb-fab" wire:click="toggleChat" title="AI Assistant">
        <span class="material-icons-round">{{ $isOpen ? 'close' : 'smart_toy' }}</span>
    </button>

    {{-- Chat panel --}}
    @if($isOpen)
    <div class="aicb-panel">

        <div class="aicb-header">
            <div class="aicb-header-title">
                <span class="material-icons-round">smart_toy</span>
                <span>AI Assistant</span>
            </div>
            <button type="button" class="aicb-btn aicb-btn-icon" wire:click="clearChat" title="Clear chat">
                <span class="material-icons-round">delete_sweep</span>
            </button>
        </div>

        <div class="aicb-body" id="aicb-scroll" wire:poll.stop>
            @forelse($chatHistory as $msg)
                <div class="aicb-bubble-row {{ $msg['role'] === 'user' ? 'aicb-row-user' : 'aicb-row-assistant' }}">
                    @if($msg['role'] === 'assistant')
                        <span class="material-icons-round aicb-bubble-icon">smart_toy</span>
                    @endif
                    <div class="aicb-bubble-text">{{ $msg['content'] }}</div>
                </div>
            @empty
                <div class="aicb-empty">
                    <span class="material-icons-round">chat</span>
                    <p>কিছু জিজ্ঞাসা করুন, আমি সাহায্য করার চেষ্টা করবো।</p>
                </div>
            @endforelse

            @if($isTyping)
                <div class="aicb-bubble-row aicb-row-assistant">
                    <span class="material-icons-round aicb-bubble-icon">smart_toy</span>
                    <div class="aicb-bubble-text aicb-typing-dots">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            @endif
        </div>

        <form wire:submit.prevent="sendMessage" class="aicb-input-row">
            <input
                type="text"
                class="aicb-input"
                placeholder="লিখুন..."
                wire:model="newMessage"
                autocomplete="off"
                {{ $isTyping ? 'disabled' : '' }}
            >
            <button type="submit" class="aicb-btn aicb-btn-send" {{ $isTyping ? 'disabled' : '' }}>
                <span class="material-icons-round">send</span>
            </button>
        </form>

    </div>
    @endif
</div>

@once
<style>
.aicb-wrapper {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
}
.aicb-fab {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, #ec4899, #db2777);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(219,39,119,0.4);
    cursor: pointer;
}
.aicb-panel {
    position: absolute;
    bottom: 70px;
    right: 0;
    width: 360px;
    max-width: 90vw;
    height: 480px;
    display: flex;
    flex-direction: column;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    background: #fff;
}
.aicb-header {
    background: linear-gradient(135deg, #ec4899, #db2777);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
}
.aicb-header-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
}
.aicb-btn {
    background: transparent;
    border: none;
    color: inherit;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.aicb-btn-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    color: #fff;
}
.aicb-btn-icon:hover {
    background: rgba(255,255,255,0.15);
}
.aicb-body {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: #f9fafb;
}
.aicb-bubble-row {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    max-width: 85%;
}
.aicb-row-user {
    align-self: flex-end;
    flex-direction: row-reverse;
}
.aicb-row-assistant {
    align-self: flex-start;
}
.aicb-bubble-text {
    padding: 8px 12px;
    border-radius: 12px;
    font-size: 14px;
    line-height: 1.4;
    white-space: pre-wrap;
}
.aicb-row-user .aicb-bubble-text {
    background: #db2777;
    color: #fff;
    border-bottom-right-radius: 2px;
}
.aicb-row-assistant .aicb-bubble-text {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-bottom-left-radius: 2px;
}
.aicb-bubble-icon {
    font-size: 20px;
    color: #db2777;
    margin-top: 4px;
}
.aicb-empty {
    margin: auto;
    text-align: center;
    color: #9ca3af;
}
.aicb-input-row {
    display: flex;
    gap: 8px;
    padding: 10px;
    border-top: 1px solid #e5e7eb;
    background: #fff;
}
.aicb-input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    font-size: 14px;
    outline: none;
}
.aicb-input:focus {
    border-color: #db2777;
}
.aicb-btn-send {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ec4899, #db2777);
    color: #fff;
    flex-shrink: 0;
}
.aicb-btn-send:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.aicb-typing-dots span {
    display: inline-block;
    width: 6px;
    height: 6px;
    margin-right: 3px;
    border-radius: 50%;
    background: #9ca3af;
    animation: aicb-blink 1.2s infinite;
}
.aicb-typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.aicb-typing-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes aicb-blink {
    0%, 80%, 100% { opacity: 0.3; }
    40% { opacity: 1; }
}
</style>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('message-sent', () => {
        const el = document.getElementById('aicb-scroll');
        setTimeout(() => {
            if (el) el.scrollTop = el.scrollHeight;
        }, 50);
        // trigger the AI call after the "typing" UI has rendered
        Livewire.dispatch('trigger-ai-reply');
    });

    document.addEventListener('livewire:updated', () => {
        const el = document.getElementById('aicb-scroll');
        if (el) el.scrollTop = el.scrollHeight;
    });
});
</script>
@endonce