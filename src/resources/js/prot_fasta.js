/**
 * 氨基酸配置数据
 * 包含：颜色, 全名, 性质分组
 * 颜色参考：Shapely / RasMol 配色方案的变体
 */
const AMINO_ACIDS = {
  A: { color: "#8cff8c", name: "Alanine", group: "Hydrophobic" },
  R: { color: "#0000ff", name: "Arginine", group: "Positive" },
  N: { color: "#ff7070", name: "Asparagine", group: "Polar" },
  D: { color: "#ff0000", name: "Aspartic Acid", group: "Negative" },
  C: { color: "#ffff00", name: "Cysteine", group: "Polar" },
  E: { color: "#ff00ff", name: "Glutamic Acid", group: "Negative" },
  Q: { color: "#ff7070", name: "Glutamine", group: "Polar" },
  G: { color: "#ffebcd", name: "Glycine", group: "Special" },
  H: { color: "#7070ff", name: "Histidine", group: "Positive" },
  I: { color: "#8cff8c", name: "Isoleucine", group: "Hydrophobic" },
  L: { color: "#8cff8c", name: "Leucine", group: "Hydrophobic" },
  K: { color: "#0000ff", name: "Lysine", group: "Positive" },
  M: { color: "#ffebcd", name: "Methionine", group: "Hydrophobic" },
  F: { color: "#8cff8c", name: "Phenylalanine", group: "Hydrophobic" },
  P: { color: "#ffebcd", name: "Proline", group: "Special" },
  S: { color: "#ff7070", name: "Serine", group: "Polar" },
  T: { color: "#ff7070", name: "Threonine", group: "Polar" },
  W: { color: "#8cff8c", name: "Tryptophan", group: "Hydrophobic" },
  Y: { color: "#8cff8c", name: "Tyrosine", group: "Hydrophobic" },
  V: { color: "#8cff8c", name: "Valine", group: "Hydrophobic" },
  // 非标准或未知字符
  X: { color: "#bebebe", name: "Unknown", group: "Other" },
  B: { color: "#ff69b4", name: "Asparagine/Aspartic", group: "Other" },
  Z: { color: "#ff69b4", name: "Glutamine/Glutamic", group: "Other" },
  "*": { color: "#ffffff", name: "Stop", group: "Special" },
  "-": { color: "#ffffff", name: "Gap", group: "Other" },
};

const containerEl = document.getElementById("visual-container");
const tooltipEl = document.getElementById("tooltip");

// 统计元素
const statLen = document.getElementById("stat-length");
const statHydro = document.getElementById("stat-hydro");
const statPolar = document.getElementById("stat-polar");

/**
 * 初始化函数：页面加载时执行
 */
function init() {
  renderLegend();

  // 1. 尝试从用户提供的 ID #fasta_seq 中提取数据
  const originalSeqSpan = document.getElementById("fasta_seq");
  let initialSequence = "";

  if (originalSeqSpan) {
    initialSequence = originalSeqSpan.innerText.trim();
    console.log("提取到用户指定ID的序列，长度:", initialSequence.length);
  } else {
  }

  // 执行首次渲染
  visualize(initialSequence);
}

/**
 * 核心可视化函数
 */
function visualize(rawInput) {
  // 清理数据：移除FASTA头部（以>开头的行）和所有空白符
  let cleanSeq = rawInput.replace(/^>.*$/gm, "").replace(/\s/g, "");

  // 如果为空，显示提示
  if (!cleanSeq) {
    containerEl.innerHTML =
      '<span style="color: #999; padding: 20px;">请输入有效的序列...</span>';
    updateStats(cleanSeq);
    return;
  }

  // 渲染序列
  renderSequence(cleanSeq);
  updateStats(cleanSeq);
}

/**
 * 渲染序列到DOM
 */
function renderSequence(seq) {
  containerEl.innerHTML = ""; // 清空容器

  // 使用 DocumentFragment 优化性能，避免多次重排
  const fragment = document.createDocumentFragment();

  for (let i = 0; i < seq.length; i++) {
    const char = seq[i].toUpperCase();
    const aaData = AMINO_ACIDS[char] || AMINO_ACIDS["X"]; // 处理未知字符

    const span = document.createElement("span");
    span.textContent = char;
    span.className = "aa-block";
    span.style.backgroundColor = aaData.color;

    // 设置数据属性以便交互
    span.dataset.index = i;
    span.dataset.char = char;
    span.dataset.name = aaData.name;
    span.dataset.group = aaData.group;

    // 事件监听
    span.addEventListener("mouseenter", showTooltip);
    span.addEventListener("mouseleave", hideTooltip);
    span.addEventListener("click", copyChar);

    fragment.appendChild(span);
  }

  containerEl.appendChild(fragment);
}

/**
 * 更新统计数据
 */
function updateStats(seq) {
  statLen.textContent = seq.length;

  let hydroCount = 0;
  let polarCount = 0;

  for (let char of seq) {
    const upperChar = char.toUpperCase();
    if (AMINO_ACIDS[upperChar]) {
      if (AMINO_ACIDS[upperChar].group === "Hydrophobic") {
        hydroCount++;
      }
      if (
        AMINO_ACIDS[upperChar].group === "Polar" ||
        AMINO_ACIDS[upperChar].group === "Positive" ||
        AMINO_ACIDS[upperChar].group === "Negative"
      ) {
        polarCount++;
      }
    }
  }

  statHydro.textContent = hydroCount;
  statPolar.textContent = polarCount;
}

/**
 * 渲染图例
 */
function renderLegend() {
  const legendEl = document.getElementById("legend");
  // 按字母顺序排序
  const keys = Object.keys(AMINO_ACIDS).sort();

  keys.forEach((key) => {
    const item = document.createElement("div");
    item.className = "legend-item";

    const colorBox = document.createElement("div");
    colorBox.className = "legend-color";
    colorBox.style.backgroundColor = AMINO_ACIDS[key].color;

    const text = document.createElement("span");
    text.textContent = `${key}:${AMINO_ACIDS[key].name}`;

    item.appendChild(colorBox);
    item.appendChild(text);
    legendEl.appendChild(item);
  });
}

/**
 * 显示 Tooltip
 */
function showTooltip(e) {
  const target = e.target;
  const content = `<strong>${target.dataset.char}</strong> -${
    target.dataset.name
  }<br>Group: ${target.dataset.group}<br>Pos:${
    parseInt(target.dataset.index) + 1
  }`;

  tooltipEl.innerHTML = content;
  tooltipEl.style.display = "block";

  // 定位
  moveTooltip(e);

  // 添加鼠标移动监听以跟随
  document.addEventListener("mousemove", moveTooltip);
}

function moveTooltip(e) {
  // 简单的偏移计算，防止溢出屏幕
  const offset = 15;
  let left = e.clientX + offset;
  let top = e.clientY + offset;

  // 检查右边界
  if (left + 150 > window.innerWidth) {
    left = e.clientX - 160;
  }
  // 检查下边界
  if (top + 60 > window.innerHeight) {
    top = e.clientY - 70;
  }

  tooltipEl.style.left = `${left}px`;
  tooltipEl.style.top = `${top}px`;
}

function hideTooltip() {
  tooltipEl.style.display = "none";
  document.removeEventListener("mousemove", moveTooltip);
}

/**
 * 点击复制单个字符
 */
function copyChar(e) {
  const char = e.target.dataset.char;
  // 使用 Clipboard API
  navigator.clipboard
    .writeText(char)
    .then(() => {
      // 可选：添加一个临时的视觉反馈
      const originalBg = e.target.style.backgroundColor;
      e.target.style.backgroundColor = "#fff";
      setTimeout(() => {
        e.target.style.backgroundColor = originalBg;
      }, 100);
    })
    .catch((err) => {
      console.error("无法复制", err);
    });
}

// 启动程序
window.addEventListener("DOMContentLoaded", init);
