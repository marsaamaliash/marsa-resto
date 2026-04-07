import "./bootstrap";

// Livewire v3 otomatis menangani Alpine.
// Jika Anda butuh akses ke Alpine di console/script lain, gunakan ini:
document.addEventListener("livewire:init", () => {
    window.Alpine = Alpine;
});

import { livewire_hot_reload } from "virtual:livewire-hot-reload";
livewire_hot_reload();
