function parseMetaboliteTable() {
  // 获取表格元素
  const table = document.getElementById("metab-source-table");
  if (!table) {
    console.error("未找到表格元素");
    return "";
  }

  // 获取所有数据行
  const rows = table.querySelectorAll("tbody tr");
  let csvContent = "id,name,zh_name,formula,exact_mass\n"; // CSV头部

  // 遍历每一行
  rows.forEach((row) => {
    const cells = row.querySelectorAll("td");

    // 提取ID（去除URL链接部分）
    const idCell = cells[0];
    let id = idCell.textContent.trim();
    // 去除可能存在的额外文本
    const linkElement = idCell.querySelector("a");
    if (linkElement) {
      id = linkElement.textContent.trim();
    }

    // 提取name（第一个链接中的英文名）
    const nameCell = cells[1];
    const nameLink = nameCell.querySelector("a");
    let name = "";
    if (nameLink) {
      // 查找英文名称（原文）
      const originSpan = nameLink.querySelector(
        ".__qingyan_web_translate_origin__"
      );
      const translatedSpan = nameLink.querySelector(
        ".__qingyan_web_translate_translated__"
      );

      if (originSpan && originSpan.style.display !== "none") {
        name = originSpan.textContent.trim();
      } else if (nameLink.childNodes.length > 0) {
        // 尝试获取直接文本节点
        for (let i = 0; i < nameLink.childNodes.length; i++) {
          const node = nameLink.childNodes[i];
          if (node.nodeType === Node.TEXT_NODE) {
            const text = node.textContent.trim();
            if (text && !text.includes("span")) {
              name = text;
              break;
            }
          }
        }
      }

      // 如果上面的方法都没获取到name，则使用链接的文本内容
      if (!name) {
        name = nameLink.textContent.trim();

        // 过滤掉翻译相关的文本
        if (name.includes("span")) {
          // 使用正则表达式提取非span部分的文本
          const textMatch = nameLink.innerHTML.match(
            /<a[^>]*>([^<]*(?:<(?!\/?span)[^>]*)*[^<]*)/
          );
          if (textMatch && textMatch[1]) {
            name = textMatch[1].trim();
          } else {
            // 提取第一个文本节点内容
            const textNodes = Array.from(nameLink.childNodes).filter(
              (node) => node.nodeType === Node.TEXT_NODE
            );
            if (textNodes.length > 0) {
              name = textNodes[0].textContent.trim();
            }
          }
        }
      }
    }

    // 提取中文名称
    let zhName = "";
    if (nameLink) {
      const translatedSpans = nameLink.querySelectorAll(
        ".__qingyan_web_translate_translated__"
      );
      if (translatedSpans.length > 0) {
        // 获取最后一个翻译span的内容（通常是中文名称）
        const lastTranslatedSpan = translatedSpans[translatedSpans.length - 1];
        if (lastTranslatedSpan && lastTranslatedSpan.style.display !== "none") {
          zhName = lastTranslatedSpan.textContent.trim();
        }
      }
    }

    // 提取formula
    const formula = cells[2].textContent.trim();

    // 提取exact mass
    const exactMass = cells[3].textContent.trim();

    // 添加到CSV内容
    csvContent += `"${id}","${name}","${zhName}","${formula}","${exactMass}"\n`;
  });

  return csvContent;
}

// 创建下载功能
function downloadCSV(content, filename) {
  const blob = new Blob([content], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");
  const url = URL.createObjectURL(blob);

  link.setAttribute("href", url);
  link.setAttribute("download", filename);
  link.style.visibility = "hidden";

  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function get_table() {
  // 执行函数并返回结果
  const csvResult = parseMetaboliteTable();
  console.log(csvResult);
  // 如果需要下载文件，取消下面的注释
  downloadCSV(csvResult, "metabolites.csv");
}
