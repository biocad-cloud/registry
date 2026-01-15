document.addEventListener("DOMContentLoaded", function () {
  // 模拟从后台获取的热门搜索词条
  const hotSearchTerms = [
    "MySQL 性能优化",
    "PostgreSQL 教程",
    "Redis 集群搭建",
    "SQL 基础查询",
    "数据库索引原理",
    "MongoDB 使用指南",
    "事务 ACID 特性",
    "Oracle 存储过程",
  ];

  const searchInput = document.getElementById("searchInput");
  const searchButton = document.getElementById("searchButton");
  const searchForm = document.getElementById("searchForm");
  const suggestionsBox = document.getElementById("suggestionsBox");
  const suggestionsList = document.getElementById("suggestionsList");

  // 1. 初始化热门搜索列表
  function renderSuggestions(filterText = "") {
    suggestionsList.innerHTML = "";

    // 简单的过滤逻辑（如果用户输入了内容，也可以显示匹配的，这里仅展示全部热门或前几个）
    const termsToShow = filterText
      ? hotSearchTerms.filter((t) =>
          t.toLowerCase().includes(filterText.toLowerCase())
        )
      : hotSearchTerms;

    if (termsToShow.length === 0) {
      suggestionsList.innerHTML =
        '<div class="p-3 text-center text-muted small">无相关推荐</div>';
      return;
    }

    termsToShow.forEach((term) => {
      const item = document.createElement("div");
      item.className = "suggestion-item";
      // 使用 search 图标作为每行的装饰
      item.innerHTML = `<i class="bi bi-search"></i> <span>${term}</span>`;

      // 点击推荐词
      item.addEventListener("click", function () {
        executeSearch(term);
      });

      suggestionsList.appendChild(item);
    });
  }

  // 2. 执行搜索的核心逻辑
  function executeSearch(query) {
    if (!query || query.trim() === "") {
      // 如果为空，可以给个提示或者仅仅聚焦
      searchInput.focus();
      return;
    }

    // 编码内容并跳转
    const encodedQuery = encodeURIComponent(query.trim());
    const targetUrl = `/search/?q=${encodedQuery}`;

    // 在实际页面中，这里是 window.location.href = targetUrl;
    // 为了演示效果，我们在控制台打印并使用一个临时的 toast 提示，而不是真的跳走
    console.log("Navigating to:", targetUrl);

    // 创建一个临时的 Toast 提示用户 (因为没有引入 toast css，这里用简单的 log 代替，实际请取消下面注释)
    // window.location.href = targetUrl;

    alert(`模拟跳转至: ${targetUrl}\n(实际部署时请使用 window.location.href)`);
  }

  // 3. 事件监听：获得焦点显示下拉框
  searchInput.addEventListener("focus", function () {
    renderSuggestions(searchInput.value);
    suggestionsBox.style.display = "block";
  });

  // 4. 事件监听：输入时更新下拉框（可选功能，增加体验）
  searchInput.addEventListener("input", function () {
    renderSuggestions(this.value);
    suggestionsBox.style.display = "block";
  });

  // 5. 事件监听：失去焦点隐藏下拉框
  // 使用 setTimeout 防止点击下拉项时，下拉框先消失导致点击无效
  searchInput.addEventListener("blur", function () {
    setTimeout(() => {
      suggestionsBox.style.display = "none";
    }, 200);
  });

  // 6. 事件监听：点击搜索按钮
  searchButton.addEventListener("click", function () {
    executeSearch(searchInput.value);
  });

  // 7. 事件监听：表单提交（按下回车）
  searchForm.addEventListener("submit", function (e) {
    e.preventDefault(); // 阻止默认表单提交，使用 JS 控制跳转
    executeSearch(searchInput.value);
  });

  // 点击页面其他地方关闭下拉框（增强体验）
  document.addEventListener("click", function (e) {
    if (!document.querySelector(".search-wrapper").contains(e.target)) {
      suggestionsBox.style.display = "none";
    }
  });
});
