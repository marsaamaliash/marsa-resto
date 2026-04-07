@props([
    'name',
    'size' => 20, // px
    'class' => '',
])

{{-- 🫣👌👍😁✌️😭🐣👥👤👁️👀😎🤓✍️🤝🕶️🫁🧠🫀👨‍🔬👨‍💻👉👈👆👇☝️🖐️🎁🛒👑💎💍🏆🏅🥇🥈🥉
                    🕹️🔔🔒🔓🔏🔐🔑🗝️🛠️🔧🪛🧪⚙️🧬🩺💉🩹💊🔬♀️♂️🪪🖥️💻🔋🪫💾🔦🔍🔎📚💰
                    📒📜🪙💴📨📦✏️📁📂🗂️🖍️🖋️📌📍📝🗑️🗄️🎯🔭🖨️💡📬📤🗃️
                    ⏰☕🚑🚀🛟⚓🛞🚧🕋🧹🧺🌡️🌞⭐✨🚏🌟⚡🔥❄️⛔🚫❌❗❕❓❔‼️⁉️💯☢️〽️❎✅
                    ♻️⚠️🌐🚺🚹☑️🔙🔚✔️💲➕➖✖️➗🟰🔻🔺🚛🚚🛩️🚦🚥➡️⬅️⬆️⬇️↪️↩️⤵️⤴️🔝
                    🔘🔴🟠🟡🟢🔵🟣🟤⚫⚪🟥🟧🟨🟩🟦🟪🟫⬛⬜🎫🎏📲📖🕰️🔁 --}}

@php
    $cls = trim("inline-block align-middle {$class}");
    $style = "width:{$size}px;height:{$size}px;";
@endphp

@if ($name === 'eye')
    {{-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="{{ $cls }}" style="{{ $style }}"
        fill="currentColor" aria-hidden="true">
        <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
    </svg> --}}
    👁️
@elseif ($name === 'edit')
    {{-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="{{ $cls }}" style="{{ $style }}"
        fill="currentColor" aria-hidden="true">
        <path
            d="M3 17.25V21h3.75l11-11-3.75-3.75-11 11zM20.7 7.04a1 1 0 0 0 0-1.41L18.37 3.3a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.82-1.84z" />
    </svg> --}}
    ✏️
@elseif ($name === 'trash')
    {{-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="{{ $cls }}" style="{{ $style }}"
        fill="currentColor" aria-hidden="true">
        <path
            d="M9 3h6l1 2h5a1 1 0 1 1 0 2h-1l-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 7H3a1 1 0 1 1 0-2h5l1-2zm1 6a1 1 0 0 0-2 0v10a1 1 0 1 0 2 0V9zm6 0a1 1 0 1 0-2 0v10a1 1 0 1 0 2 0V9z" />
    </svg> --}}
    🗑️
@elseif ($name === 'plus')
    {{-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="{{ $cls }}" style="{{ $style }}"
        fill="currentColor" aria-hidden="true">
        <path d="M11 5a1 1 0 1 1 2 0v6h6a1 1 0 1 1 0 2h-6v6a1 1 0 1 1-2 0v-6H5a1 1 0 1 1 0-2h6V5z" />
    </svg> --}}
    ➕
@elseif ($name === 'approve')
    {{-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="{{ $cls }}" style="{{ $style }}"
        fill="currentColor" aria-hidden="true">
        <path d="M9 16.2 4.8 12a1 1 0 1 1 1.4-1.4L9 13.4l8.8-8.8a1 1 0 1 1 1.4 1.4L9 16.2z" />
    </svg> --}}
    ✅
@elseif ($name === 'reject')
    {{-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="{{ $cls }}" style="{{ $style }}"
        fill="currentColor" aria-hidden="true">
        <path
            d="M18.3 5.7a1 1 0 0 1 0 1.4L13.4 12l4.9 4.9a1 1 0 0 1-1.4 1.4L12 13.4l-4.9 4.9a1 1 0 0 1-1.4-1.4L10.6 12 5.7 7.1A1 1 0 0 1 7.1 5.7L12 10.6l4.9-4.9a1 1 0 0 1 1.4 0z" />
    </svg> --}}
    ⚡
@elseif ($name === 'cari')
    👀
@elseif ($name === 'clear')
    🧹
@elseif ($name === 'exportfiltered')
    🛩️
@elseif ($name === 'exportselected')
    🚀
@elseif ($name === 'printselected')
    🗃️
@endif
