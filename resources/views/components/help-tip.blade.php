@props(['text'])

<span class="help-tip" tabindex="0" onclick="event.stopPropagation(); window.toggleHelpTip(this)">
    <i class="fas fa-question-circle"></i>
    <span class="help-tip-bubble">{{ $text }}</span>
</span>
