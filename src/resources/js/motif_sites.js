document.addEventListener("DOMContentLoaded", function () {
  const rawDataElement = document.getElementById("sites");
  const container = document.getElementById("visualization-container");

  if (!rawDataElement) {
    container.innerHTML =
      '<p style="color:red; text-align:center;">错误：未找到 id 为 "sites" 的 JSON 数据。</p>';
    return;
  }

  try {
    const sitesData = JSON.parse(rawDataElement.textContent);

    sitesData.forEach((item) => {
      const rowDiv = document.createElement("div");
      rowDiv.className = "sequence-row";

      const nameDiv = document.createElement("div");
      nameDiv.className = "site-name";
      nameDiv.textContent = item.name;

      const seqDiv = document.createElement("div");
      seqDiv.className = "site-sequence";
      seqDiv.innerHTML = colorizeSequence(item.sequence);

      rowDiv.appendChild(nameDiv);
      rowDiv.appendChild(seqDiv);
      container.appendChild(rowDiv);
    });

    if (sitesData.length === 0) {
      container.innerHTML = '<p style="text-align:center;">暂无数据。</p>';
    }
  } catch (error) {
    console.error("JSON 解析失败:", error);
    container.innerHTML =
      '<p style="color:red; text-align:center;">JSON 数据格式错误，请检查控制台。</p>';
  }
});

function colorizeSequence(sequence) {
  return sequence
    .split("")
    .map((base) => {
      const baseUpper = base.toUpperCase();
      const className = ["A", "T", "C", "G"].includes(baseUpper)
        ? `base base-${baseUpper}`
        : "base";
      return `<span class="${className}">${base}</span>`;
    })
    .join("");
}
