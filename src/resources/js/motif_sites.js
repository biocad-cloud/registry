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

// 全局变量存储计算结果
let pwmResult = null;
let sequences = [];

// 页面加载完成后初始化
document.addEventListener("DOMContentLoaded", function () {
  loadSequences();
  calculatePWM();
  // generateMEMEFormat();
});

// 加载序列数据
function loadSequences() {
  const rawDataElement = document.getElementById("sites");
  if (rawDataElement) {
    try {
      const sitesData = JSON.parse(rawDataElement.textContent);
      sequences = sitesData.map((item) => item.sequence.toUpperCase());
      updateStatistics();
    } catch (error) {
      console.error("JSON解析失败:", error);
      alert("数据加载失败，请检查JSON格式");
    }
  }
}

// 更新统计信息
function updateStatistics() {
  document.getElementById("seq-count").textContent = sequences.length;
  if (sequences.length > 0) {
    document.getElementById("seq-length").textContent = sequences[0].length;
    document.getElementById("total-sites").textContent =
      sequences.length * sequences[0].length;

    // 计算GC含量
    let gcCount = 0;
    let totalBases = 0;
    sequences.forEach((seq) => {
      for (let base of seq) {
        if (base === "G" || base === "C") {
          gcCount++;
        }
        totalBases++;
      }
    });
    const gcContent = ((gcCount / totalBases) * 100).toFixed(1);
    document.getElementById("gc-content").textContent = gcContent + "%";
  }
}

// 计算PWM矩阵
function calculatePWM() {
  if (sequences.length === 0) {
    alert("请先加载序列数据");
    return;
  }

  const length = sequences[0].length;
  const numSequences = sequences.length;

  // 验证所有序列长度是否一致
  const allSameLength = sequences.every((seq) => seq.length === length);
  if (!allSameLength) {
    alert("所有序列长度必须一致才能计算PWM矩阵");
    return;
  }

  // 初始化计数矩阵 [位置][碱基索引]
  // 碱基顺序: A=0, C=1, G=2, T=3
  const counts = Array.from({ length: length }, () => [0, 0, 0, 0]);

  // 统计每个位置上每个碱基的出现次数
  sequences.forEach((seq) => {
    for (let i = 0; i < length; i++) {
      const base = seq[i];
      let baseIndex;
      switch (base) {
        case "A":
          baseIndex = 0;
          break;
        case "C":
          baseIndex = 1;
          break;
        case "G":
          baseIndex = 2;
          break;
        case "T":
          baseIndex = 3;
          break;
        default:
          continue; // 跳过非ATCG的碱基
      }
      counts[i][baseIndex]++;
    }
  });

  // 计算频率矩阵，使用伪计数避免零概率
  const pseudocount = 0.25; // 伪计数值
  const pwm = [];

  for (let i = 0; i < length; i++) {
    const totalCount = counts[i].reduce((a, b) => a + b, 0) + pseudocount * 4;
    const row = counts[i].map((count) => (count + pseudocount) / totalCount);
    pwm.push(row);
  }

  // 存储计算结果
  pwmResult = {
    pwm: pwm,
    length: length,
    numSequences: numSequences,
    counts: counts,
  };

  // 显示计算结果
  // displayPWMResult();

  // 生成MEME格式
  // generateMEMEFormat();
}

// 显示PWM计算结果
function displayPWMResult() {
  const outputDiv = document.getElementById("pwm-output");
  if (!pwmResult) {
    outputDiv.textContent = "请先计算PWM矩阵";
    return;
  }

  let resultText = `PWM矩阵计算完成:\n\n`;
  resultText += `矩阵维度: ${pwmResult.length} × 4\n`;
  resultText += `序列数量: ${pwmResult.numSequences}\n`;
  resultText += `伪计数: 0.25\n\n`;

  resultText += `位置\tA\t\tC\t\tG\t\tT\n`;
  resultText += `------------------------------------------------\n`;

  for (let i = 0; i < pwmResult.length; i++) {
    const row = pwmResult.pwm[i];
    resultText += `${i + 1}\t`;
    resultText += row.map((val) => val.toFixed(6).padStart(10)).join("\t");
    resultText += "\n";
  }

  outputDiv.textContent = resultText;
}

// 生成MEME格式的PWM数据
function generateMEMEFormat() {
  if (!pwmResult) {
    alert("请先计算PWM矩阵");
    return;
  }

  // MEME格式头部
  let memeFormat = `letter-probability matrix: alength= 4 w= ${pwmResult.length} nsites=${pwmResult.numSequences} E= 0.0\n`;

  // 添加PWM矩阵数据
  for (let i = 0; i < pwmResult.length; i++) {
    const row = pwmResult.pwm[i];
    const rowStr = row.map((val) => val.toFixed(6)).join(" ");
    memeFormat += rowStr + "\n";
  }

  // 更新显示
  const outputDiv = document.getElementById("pwm-output");
  outputDiv.textContent = memeFormat;

  // 更新Logo预览区域
  updateLogoPreview(memeFormat);
}

// 更新Logo预览
function updateLogoPreview(memeFormat) {
  const previewDiv = document.getElementById("logo-preview");
  previewDiv.innerHTML = `
        <div style="text-align: center;">
            <h3>PWM数据已生成</h3>
            <p>以下数据可用于绘制Sequence Logo:</p>
            <pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; text-align: left; overflow-x: auto;">${memeFormat}</pre>
            <p style="color: #666; font-size: 14px; margin-top: 15px;">
                注意: 实际的Sequence Logo绘制需要专门的JavaScript库<br>
                (如 motifLogo.js, d3.js, WebLogo等)
            </p>
        </div>
    `;
}

// 重置数据
function resetData() {
  pwmResult = null;
  document.getElementById("pwm-output").textContent =
    '点击"计算PWM矩阵"按钮开始计算...';
  document.getElementById("logo-preview").innerHTML =
    "<p>生成PWM矩阵后，这里将显示Sequence Logo</p>";
}

// 辅助函数：计算信息含量
function calculateInformationContent(pwm) {
  const backgroundFreq = [0.25, 0.25, 0.25, 0.25]; // 背景频率
  const ic = [];

  for (let i = 0; i < pwm.length; i++) {
    let positionIC = 2; // 最大信息含量 (log2(4))
    for (let j = 0; j < 4; j++) {
      if (pwm[i][j] > 0) {
        positionIC += pwm[i][j] * Math.log2(pwm[i][j] / backgroundFreq[j]);
      }
    }
    ic.push(Math.max(0, positionIC)); // 确保不为负数
  }

  return ic;
}

// 辅助函数：计算保守性
function calculateConservation(pwm) {
  const conservation = [];

  for (let i = 0; i < pwm.length; i++) {
    const maxProb = Math.max(...pwm[i]);
    conservation.push(maxProb);
  }

  return conservation;
}
