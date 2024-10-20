@isset($support_ticket)
    @if ($support_ticket->status != support_ticket_const()::SOLVED)
        <div class="chat-form">
            <div class="publisher">
                <div class="chatbox-message-part">
                    <input class="publisher-input message-input message-input-event" type="text" name="message" placeholder="{{ __("Write something....") }}">
                </div>
                <div class="chatbox-send-part">
                    <button type="button" class="chat-submit-btn chat-submit-btn-event"><i class="lab la-telegram-plane"></i></button>
                </div>
            </div>
        </div>
    @else
        <div class="solved-message py-3 px-4 text-warning">{{ __("This ticket is solved, you can't send message right now.") }}</div>
    @endif
@endisset
