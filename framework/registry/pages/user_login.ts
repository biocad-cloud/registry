namespace pages {

    export class user_login extends Bootstrap {

        get appName(): string {
            return "user_login";
        }

        protected init(): void {

        }

        public login_onclick() {
            const email = (<HTMLInputElement><any>$ts("#loginEmail")).value;
            const password = (<HTMLInputElement><any>$ts("#loginPassword")).value;
            const loginForm = document.getElementById("loginForm");

            if (email && password) {
                // Simulate login
                const btn = <HTMLButtonElement>loginForm.querySelector('button[id="login"]');
                const originalText = btn.textContent;

                btn.textContent = "登录中...";
                btn.disabled = true;

                $ts.post("/user/login/", { email: email, passwd: md5(password) }, result => {
                    if (result.code == 0) {
                        $goto("/user/home/");
                    } else {
                        btn.textContent = originalText;
                        btn.disabled = false;

                        msgbox.showMessageModal(result.info);
                    }
                });
            } else {
                msgbox.showMessageModal("Email or Password is required!");
            }
        }
    }
}