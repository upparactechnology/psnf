<?php $layout = 'parent'; ?>

<div class="space-y-6 max-w-4xl">

    <!-- Recipient details bar -->
    <div class="p-4 rounded-2xl border bg-white dark:bg-slate-900/30 border-slate-200 dark:border-white/5 flex items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                S
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white"><?= e($staff['name']) ?></h3>
                <p class="text-[10px] text-slate-500 dark:text-slate-400">School Representative (Admin / Teacher)</p>
            </div>
        </div>
        <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/10 px-2 py-0.5 rounded-full">
            Online Support
        </span>
    </div>

    <!-- Chat container -->
    <div class="rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/20 p-5 flex flex-col h-[500px] shadow-sm">

        <!-- Scrollable messages thread -->
        <div id="chat-messages-box" class="flex-1 overflow-y-auto pr-2 mb-4 scroll-smooth">
            <?php if (empty($messages)): ?>
            <div class="h-full flex flex-col items-center justify-center text-center">
                <svg class="w-8 h-8 text-slate-400 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <p class="text-xs text-slate-500">Start a new message thread with the school.</p>
            </div>
            <?php else: ?>
            <?php foreach ($messages as $msg): ?>
            <?php
            // Check if sender is current logged in parent user
            $isMe = (int)$msg['sender_id'] === (int)$guardian['user_id'];
            $align = $isMe ? 'justify-end' : 'justify-start';
            $color = $isMe ? 'bg-indigo-600 text-white rounded-br-none' : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-bl-none';
            ?>
            <div class="flex <?= $align ?> gap-3 mb-4">
                <div class="max-w-[70%] p-3.5 rounded-2xl shadow-sm <?= $color ?>">
                    <p class="text-xs leading-relaxed"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                    <span class="block text-[9px] text-right mt-1 opacity-70"><?= date('h:i A', strtotime($msg['created_at'])) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Message input box -->
        <form hx-post="<?= url('parent/communication') ?>"
              hx-swap="innerHTML"
              hx-target="#chat-messages-box"
              @submit="setTimeout(() => { $refs.messageInput.value = ''; $refs.box.scrollTop = $refs.box.scrollHeight; }, 100)"
              class="flex gap-3 pt-3 border-t border-slate-200 dark:border-slate-800/80">

            <textarea name="message" required x-ref="messageInput"
                      rows="1"
                      @keydown.enter.prevent="if ($el.value.trim()) { $el.form.dispatchEvent(new Event('submit')); }"
                      class="flex-1 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-850 text-slate-850 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-xl py-3 px-4 text-xs focus:outline-none focus:border-brand-500 resize-none"
                      placeholder="Type a message or school query... (Press Enter to send)"></textarea>

            <button type="submit"
                    class="px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center flex-shrink-0">
                Send
            </button>
        </form>

    </div>

</div>

<!-- Auto-scroll script -->
<script>
    window.addEventListener('load', () => {
        const box = document.getElementById('chat-messages-box');
        if (box) box.scrollTop = box.scrollHeight;
    });
    document.body.addEventListener('htmx:afterOnLoad', () => {
        const box = document.getElementById('chat-messages-box');
        if (box) box.scrollTop = box.scrollHeight;
    });
</script>
