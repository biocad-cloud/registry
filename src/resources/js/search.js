document.getElementById("search-submit").onclick = function () {
  let searchInput = document.getElementById("q");
  let query = searchInput.value.trim();

  if (query) {
    // 核心需求：编码URL并跳转
    // 使用 encodeURIComponent 对特殊字符进行转义，确保 URL 安全
    window.location.href = `/search/?q=${encodeURIComponent(query)}`;
  }
};
