import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        // 'this.element' représente le select sur lequel le contrôleur est branché
        $(this.element).select2({
            width: "100%", // Optionnel mais fortement recommandé pour le design
        });
    }

    disconnect() {
        // On nettoie proprement si l'élément est supprimé du DOM
        $(this.element).select2("destroy");
    }
}
