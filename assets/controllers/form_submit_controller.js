import { Controller } from "@hotwired/stimulus";
import { getComponent } from "@symfony/ux-live-component";

export default class extends Controller {
    static targets = ["form"];

    async initialize() {
        this.component = await getComponent(this.element);

        this.component.on("model:set", function (model, value, component) {
            component.performRequest()
            component.action("save").then(() => {});
        });
    }
}
