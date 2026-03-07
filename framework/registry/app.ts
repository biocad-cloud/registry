///<reference path="../linq.d.ts" />

namespace app {

    export function run() {
        Router.AddAppHandler(new pages.spectrum_data());
        Router.AddAppHandler(new pages.taxonomy_data());
        Router.AddAppHandler(new pages.metabolite_data());

        Router.AddAppHandler(new pages.landscapes.metabolic());

        Router.AddAppHandler(new pages.user_login());

        Router.RunApp();
    }
}

$ts.mode = Modes.debug;
$ts(app.run);