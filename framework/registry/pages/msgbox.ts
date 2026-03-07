module msgbox {

    // 辅助函数：显示消息对话框
    export function showMessageModal(message, title = "Error Message", type: "error" | "success" | "warning" = "error") {
        const modalEl = document.getElementById('messageModal');
        const contentEl = document.getElementById('messageModalContent');
        const titleEl = document.getElementById('messageModalLabel');
        const textEl = document.getElementById('messageModalText');

        // 清除之前的状态类
        contentEl.classList.remove('error', 'success', 'warning');

        // 隐藏所有图标
        contentEl.querySelectorAll('.icon-wrapper svg').forEach(icon => icon.classList.add('d-none'));

        // 设置当前状态
        contentEl.classList.add(type);
        titleEl.textContent = title;
        textEl.textContent = message;

        // 显示对应图标
        if (type === 'error') {
            contentEl.querySelector('.error-icon').classList.remove('d-none');
        } else if (type === 'success') {
            contentEl.querySelector('.success-icon').classList.remove('d-none');
        } else if (type === 'warning') {
            contentEl.querySelector('.warning-icon').classList.remove('d-none');
        }

        // 显示 Modal
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}