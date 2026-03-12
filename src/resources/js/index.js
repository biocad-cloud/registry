document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchInput");
  const searchButton = document.getElementById("searchButton");
  const searchForm = document.getElementById("searchForm");
  const suggestionsBox = document.getElementById("suggestionsBox");
  const suggestionsList = document.getElementById("suggestionsList");

  // 用于缓存后台获取的热门搜索数据
  let hotSearchCache = [];

  // 1. 渲染建议列表 (适配新的JSON结构)
  function renderSuggestions(dataList) {
    suggestionsList.innerHTML = "";

    if (!dataList || dataList.length === 0) {
      suggestionsList.innerHTML =
        '<div class="p-3 text-center text-muted small">暂无热门推荐</div>';
      return;
    }

    dataList.forEach((item) => {
      // item 结构: { term: "...", hits: "..." }
      const div = document.createElement("div");
      div.className = "suggestion-item";

      // 左侧：图标 + 词条
      const termSpan = document.createElement("span");
      termSpan.className = "suggestion-term";
      termSpan.innerHTML = `<i class="bi bi-search"></i> <span>${item.term}</span>`;

      // 右侧：热度
      // const hitsSpan = document.createElement("span");
      // hitsSpan.className = "suggestion-hits";
      // hitsSpan.textContent = `${item.hits} 次`; // 显示热度

      div.appendChild(termSpan);
      // div.appendChild(hitsSpan);

      // 点击事件：执行搜索
      div.addEventListener("click", function () {
        executeSearch(item.term);
      });

      suggestionsList.appendChild(div);
    });
  }

  // 2. 从后端 API 获取热门搜索
  async function fetchHotSearch() {
    try {
      // 发起 GET 请求
      const response = await fetch("/registry/hot_search/");

      if (!response.ok) {
        throw new Error("Network response was not ok");
      }

      const jsonData = await response.json();

      // 判断返回状态码
      if (jsonData.code === 0 && jsonData.info) {
        hotSearchCache = jsonData.info; // 缓存数据
        renderSuggestions(hotSearchCache); // 渲染列表
        suggestionsBox.style.display = "block";
      } else {
        console.error("API Error:", jsonData);
        // 即使API报错，也可以选择不显示下拉框或显示错误提示
      }
    } catch (error) {
      console.error("Failed to fetch hot search:", error);
      // 请求失败时的处理（可选：显示错误提示或静默失败）
    }
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
    window.location.href = targetUrl;
  }

  // 4. 事件监听：获得焦点
  searchInput.addEventListener("focus", function () {
    // 如果缓存为空，则请求后台；否则直接显示缓存（优化性能）
    if (hotSearchCache.length === 0) {
      fetchHotSearch();
    } else {
      // 如果用户之前输入过内容过滤了列表，重新聚焦时应恢复完整列表
      renderSuggestions(hotSearchCache);
      suggestionsBox.style.display = "block";
    }
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
