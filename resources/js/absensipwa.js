const $ = (id) => document.getElementById(id);

function getSelectedOffice() {
    const sel = $("id_holding");
    const opt = sel.options[sel.selectedIndex];
    return {
        id: parseInt(opt.value, 10),
        lat: parseFloat(opt.dataset.lat),
        lon: parseFloat(opt.dataset.lon),
        radius: parseInt(opt.dataset.radius, 10),
    };
}

function submitAbsensi(type, coords) {
    const office = getSelectedOffice();
    const jenis = type === "in" ? "In" : "Out";

    if (window.Livewire) {
        const component = Livewire.first();
        if (component) {
            component.call("absen", jenis, coords.latitude, coords.longitude);
            return;
        }
    }

    alert("Livewire tidak tersedia. Silakan refresh halaman.");
}

function getLocationAndSubmit(type) {
    if (!("geolocation" in navigator)) {
        alert("Geolocation tidak tersedia pada perangkat ini.");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            submitAbsensi(type, pos.coords);
        },
        (err) => {
            console.error(err);
            alert(
                "Gagal mengambil lokasi. Pastikan GPS aktif & izin lokasi diberikan.",
            );
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 },
    );
}

document.addEventListener("DOMContentLoaded", () => {
    const btnIn = $("btn-in");
    const btnOut = $("btn-out");

    btnIn?.addEventListener("click", (e) => {
        e.preventDefault();
        getLocationAndSubmit("in");
    });

    btnOut?.addEventListener("click", (e) => {
        e.preventDefault();
        getLocationAndSubmit("out");
    });
});
