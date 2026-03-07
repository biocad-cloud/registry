// Initialize background elements after DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  initDNAStrand("dnaLeft", true);
  initDNAStrand("dnaRight", false);
  initFloatingCells();
  initPasswordToggles();
  initFormValidation();
});

// Create DNA Helix
function initDNAStrand(containerId, isLeft) {
  const container = document.getElementById(containerId);
  if (!container) return;

  const nodeCount = 40;
  const spacing = 60;

  for (let i = 0; i < nodeCount; i++) {
    const node1 = document.createElement("div");
    const node2 = document.createElement("div");
    const connector = document.createElement("div");

    const y = i * spacing;
    const phase = i * 0.4;
    const x1 = Math.sin(phase) * 30 + 50;
    const x2 = Math.sin(phase + Math.PI) * 30 + 50;

    node1.className = "dna-node";
    node1.style.top = y + "px";
    node1.style.left = x1 + "%";
    node1.style.animationDelay = i * 0.1 + "s";

    node2.className = "dna-node";
    node2.style.top = y + "px";
    node2.style.left = x2 + "%";
    node2.style.animationDelay = i * 0.1 + 0.2 + "s";

    connector.className = "dna-connector";
    connector.style.top = y + 6 + "px";
    connector.style.left = Math.min(x1, x2) + "%";
    connector.style.width = Math.abs(x2 - x1) + "%";

    container.appendChild(connector);
    container.appendChild(node1);
    container.appendChild(node2);
  }
}

// Create Floating Cells
function initFloatingCells() {
  const container = document.getElementById("cellsContainer");
  if (!container) return;

  const cellCount = 8;

  for (let i = 0; i < cellCount; i++) {
    const cell = document.createElement("div");
    cell.className = "cell";

    const size = Math.random() * 80 + 40;
    const top = Math.random() * 100;
    const left = Math.random() * 100;
    const delay = Math.random() * 10;
    const duration = 15 + Math.random() * 15;

    cell.style.width = size + "px";
    cell.style.height = size + "px";
    cell.style.top = top + "%";
    cell.style.left = left + "%";
    cell.style.animationDelay = delay + "s";
    cell.style.animationDuration = duration + "s";

    container.appendChild(cell);
  }
}

// Password Toggle Functionality
function initPasswordToggles() {
  const toggles = document.querySelectorAll(".password-toggle");

  toggles.forEach((toggle) => {
    toggle.addEventListener("click", function () {
      const targetId = this.getAttribute("data-target");
      const input = document.getElementById(targetId);
      const eyeIcon = this.querySelector(".eye-icon");
      const eyeOffIcon = this.querySelector(".eye-off-icon");

      if (input && eyeIcon && eyeOffIcon) {
        if (input.type === "password") {
          input.type = "text";
          eyeIcon.classList.add("d-none");
          eyeOffIcon.classList.remove("d-none");
          this.setAttribute("aria-label", "隐藏密码");
        } else {
          input.type = "password";
          eyeIcon.classList.remove("d-none");
          eyeOffIcon.classList.add("d-none");
          this.setAttribute("aria-label", "显示密码");
        }
      }
    });
  });
}

// Form Validation & Submit
function initFormValidation() {

  // Register Form
  const registerForm = document.getElementById("registerForm");
  if (registerForm) {
    registerForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const password = document.getElementById("regPassword").value;
      const confirmPassword =
        document.getElementById("regConfirmPassword").value;

      if (password !== confirmPassword) {
        alert("两次输入的密码不一致，请重新输入。");
        return;
      }

      if (password.length < 8) {
        alert("密码长度至少为8位。");
        return;
      }

      // Simulate registration
      const btn = document.querySelector(
        '#registerModal button[type="submit"]'
      );
      btn.textContent = "注册中...";
      btn.disabled = true;

      setTimeout(() => {
        alert("注册成功！请查收验证邮件。");
        btn.textContent = "注册账号";
        btn.disabled = false;
        bootstrap.Modal.getInstance(
          document.getElementById("registerModal")
        ).hide();
      }, 1500);
    });
  }

  // Forgot Password Form
  const forgotForm = document.getElementById("forgotPasswordForm");
  if (forgotForm) {
    forgotForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const email = document.getElementById("forgotEmail").value;

      if (email) {
        const btn = document.querySelector(
          '#forgotPasswordModal button[type="submit"]'
        );
        btn.textContent = "发送中...";
        btn.disabled = true;

        setTimeout(() => {
          alert("密码重置链接已发送到您的邮箱，请注意查收。");
          btn.textContent = "发送重置链接";
          btn.disabled = false;
          bootstrap.Modal.getInstance(
            document.getElementById("forgotPasswordModal")
          ).hide();
        }, 1500);
      }
    });
  }
}
