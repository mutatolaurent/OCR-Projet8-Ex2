import $ from "jquery";
// On force jQuery à être visible globalement pour les plugins comme Select2
window.$ = window.jQuery = $;

// 2. On charge Select2 de manière dynamique (compatible ES6)
import("select2").then(() => {
    // 3. Une fois que Select2 est chargé et s'est greffé sur notre $, on initialise
    $(document).ready(function () {
        $("select[multiple]").select2();
    });
});
