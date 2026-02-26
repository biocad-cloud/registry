///<reference path="../linq.d.ts" />

namespace app {

    export function run() {
        Router.AddAppHandler(new pages.spectrum_data());
        Router.AddAppHandler(new pages.taxonomy_data());

        Router.RunApp();
    }
}

$ts.mode = Modes.debug;
$ts(app.run);