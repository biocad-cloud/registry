// ============================================
// EC Number Main Classes (Hardcoded)
// ============================================
const EC_MAIN_CLASSES = [
  {
    id: 1,
    name: "Oxidoreductases",
    description: "Catalyze oxidation-reduction reactions",
  },
  {
    id: 2,
    name: "Transferases",
    description: "Transfer functional groups between molecules",
  },
  {
    id: 3,
    name: "Hydrolases",
    description: "Catalyze hydrolysis of various bonds",
  },
  {
    id: 4,
    name: "Lyases",
    description: "Cleave bonds by means other than hydrolysis or oxidation",
  },
  {
    id: 5,
    name: "Isomerases",
    description: "Catalyze isomerization reactions",
  },
  { id: 6, name: "Ligases", description: "Join molecules using ATP" },
  {
    id: 7,
    name: "Translocases",
    description: "Catalyze movement of ions or molecules across membranes",
  },
];

// ============================================
// API Configuration
// ============================================
const API_BASE_URL = "/registry/enzyme_data/";

// ============================================
// State Management
// ============================================
const state = {
  loadedNodes: new Set(),
  expandedNodes: new Set(),
  enzymeCache: new Map(),
  totalEnzymes: 0,
  currentDetail: null,
};

// ============================================
// DOM Elements
// ============================================
const elements = {
  treeRoot: document.getElementById("treeRoot"),
  searchInput: document.getElementById("searchInput"),
  totalEnzymes: document.getElementById("totalEnzymes"),
  loadedNodes: document.getElementById("loadedNodes"),
  expandAll: document.getElementById("expandAll"),
  collapseAll: document.getElementById("collapseAll"),
  detailPanel: document.getElementById("detailPanel"),
  detailOverlay: document.getElementById("detailOverlay"),
  detailClose: document.getElementById("detailClose"),
  detailEc: document.getElementById("detailEc"),
  detailName: document.getElementById("detailName"),
  detailBody: document.getElementById("detailBody"),
};

// ============================================
// API Functions
// ============================================
async function fetchEnzymeData(ecPath) {
  const url = `${API_BASE_URL}?q=${ecPath.toString().replace(/^\.+|\.+$/g, '')}`;
  try {
    const response = await fetch(url);
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    const data = await response.json();
    return data.info || data;
  } catch (error) {
    console.error("API Error:", error);
    return null;
  }
}

// ============================================
// Tree Node Rendering
// ============================================
function createTreeNode(nodeData, level, parentPath) {
  const li = document.createElement("li");
  li.className = "tree-node";
  li.dataset.level = level;
  li.dataset.path = parentPath;

  const content = document.createElement("div");
  content.className = "tree-node-content";

  const isLeaf = level === "enzyme";
  const hasChildren = !isLeaf;

  // Toggle icon
  const toggleIcon = document.createElement("span");
  toggleIcon.className = `toggle-icon ${hasChildren ? "" : "leaf"}`;
  if (hasChildren) {
    toggleIcon.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        `;
  }

  // Node icon
  const nodeIcon = document.createElement("span");
  nodeIcon.className = "node-icon";
  if (level === "class") {
    nodeIcon.classList.add("class-icon");
    nodeIcon.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        `;
  } else if (level === "enzyme") {
    nodeIcon.classList.add("enzyme-icon");
    nodeIcon.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
        `;
  } else {
    nodeIcon.classList.add("subclass-icon");
    nodeIcon.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
        `;
  }

  // Label
  const label = document.createElement("div");
  label.className = "node-label";

  if (level === "enzyme") {
    const ecNumber = document.createElement("div");
    ecNumber.className = "node-ec";
    ecNumber.textContent = nodeData.ec_number;
    label.appendChild(ecNumber);

    const title = document.createElement("div");
    title.className = "node-title";
    title.textContent = nodeData.recommended_name || "Unnamed Enzyme";
    label.appendChild(title);

    if (nodeData.systematic_name) {
      const meta = document.createElement("div");
      meta.className = "node-meta";
      meta.textContent = nodeData.systematic_name;
      label.appendChild(meta);
    }
  } else {
    const title = document.createElement("div");
    title.className = "node-title";
    title.textContent = nodeData.name || `Subclass ${nodeData.id}`;
    label.appendChild(title);

    if (nodeData.description) {
      const desc = document.createElement("div");
      desc.className = "node-description";
      desc.textContent = nodeData.description;
      label.appendChild(desc);
    }

    if (nodeData.count !== undefined) {
      const badge = document.createElement("span");
      badge.className = "badge-count";
      badge.textContent = nodeData.count;
      title.appendChild(badge);
    }
  }

  content.appendChild(toggleIcon);
  content.appendChild(nodeIcon);
  content.appendChild(label);
  li.appendChild(content);

  // Children container
  const childrenContainer = document.createElement("ul");
  childrenContainer.className = "tree-children";
  li.appendChild(childrenContainer);

  // Click handler
  content.addEventListener("click", () =>
    handleNodeClick(
      nodeData,
      level,
      parentPath,
      li,
      toggleIcon,
      childrenContainer
    )
  );

  return li;
}

// ============================================
// Node Click Handler
// ============================================
async function handleNodeClick(
  nodeData,
  level,
  parentPath,
  li,
  toggleIcon,
  childrenContainer
) {
  const content = li.querySelector(".tree-node-content");

  if (level === "enzyme") {
    showEnzymeDetail(nodeData);
    return;
  }

  const nodeKey = `${parentPath}.${nodeData.id}`;

  // Toggle expansion
  if (state.expandedNodes.has(nodeKey)) {
    // Collapse
    state.expandedNodes.delete(nodeKey);
    toggleIcon.classList.remove("expanded");
    childrenContainer.classList.remove("expanded");
    content.classList.remove("active");
  } else {
    // Expand
    state.expandedNodes.add(nodeKey);
    toggleIcon.classList.add("expanded");
    childrenContainer.classList.add("expanded");
    content.classList.add("active");

    // Load children if not already loaded
    if (!state.loadedNodes.has(nodeKey)) {
      await loadChildren(nodeKey, level, childrenContainer);
    }
  }
}

// ============================================
// Load Children
// ============================================
async function loadChildren(nodeKey, level, container) {
  const content = container.previousElementSibling;
  const originalIcon = content.querySelector(".toggle-icon");

  // Show loading state
  content.classList.add("loading");
  originalIcon.innerHTML = '<div class="spinner"></div>';

  const data = await fetchEnzymeData(nodeKey);

  if (data) {
    if (Array.isArray(data)) {
      data.forEach((item) => {
        let nextLevel;
        if (level === "class") nextLevel = "subclass";
        else if (level === "subclass") nextLevel = "subcategory";
        else nextLevel = "enzyme";

        const childNode = createTreeNode(
          nextLevel === "enzyme" ? item : { id: item, count: null },
          nextLevel,
          nodeKey
        );
        container.appendChild(childNode);
      });
    }
    state.loadedNodes.add(nodeKey);
    updateLoadedCount();
  } else {
    const errorMsg = document.createElement("div");
    errorMsg.className = "error-message";
    errorMsg.textContent = "Failed to load data";
    container.appendChild(errorMsg);
  }

  // Restore icon
  content.classList.remove("loading");
  originalIcon.innerHTML = `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
    `;
}

// ============================================
// Enzyme Detail Panel
// ============================================
function showEnzymeDetail(enzyme) {
  state.currentDetail = enzyme;

  elements.detailEc.textContent = enzyme.ec_number;
  elements.detailName.textContent = enzyme.recommended_name || "Unknown Enzyme";

  let bodyHTML = "";

  if (enzyme.systematic_name) {
    bodyHTML += `
            <div class="detail-section">
                <div class="detail-label">Systematic Name</div>
                <div class="detail-value">${enzyme.systematic_name}</div>
            </div>
        `;
  }

  if (enzyme.note) {
    bodyHTML += `
            <div class="detail-section">
                <div class="detail-label">Notes</div>
                <div class="detail-value">${enzyme.note}</div>
            </div>
        `;
  }

  bodyHTML += `
        <div class="detail-section">
            <div class="detail-label">Database ID</div>
            <div class="detail-value">${enzyme.id}</div>
        </div>
    `;

  elements.detailBody.innerHTML = bodyHTML;
  elements.detailPanel.classList.add("open");
  elements.detailOverlay.classList.add("visible");
}

function closeDetailPanel() {
  elements.detailPanel.classList.remove("open");
  elements.detailOverlay.classList.remove("visible");
  state.currentDetail = null;
}

// ============================================
// Initialize Tree
// ============================================
function initializeTree() {
  EC_MAIN_CLASSES.forEach((cls) => {
    const node = createTreeNode(cls, "class", "");
    node.dataset.path = cls.id;
    elements.treeRoot.appendChild(node);
  });
}

// ============================================
// Update Stats
// ============================================
function updateLoadedCount() {
  elements.loadedNodes.textContent = state.loadedNodes.size;
}

// ============================================
// Search Functionality
// ============================================
async function handleSearch(query) {
  if (!query.trim()) return;

  // Parse EC number
  const parts = query.split(".").filter((p) => p);
  if (parts.length < 1) return;

  // Clear current tree state
  elements.treeRoot.innerHTML = "";
  state.loadedNodes.clear();
  state.expandedNodes.clear();

  // Find and expand to the searched node
  const mainClass = parseInt(parts[0]);
  const mainClassData = EC_MAIN_CLASSES.find((c) => c.id === mainClass);

  if (!mainClassData) return;

  const mainNode = createTreeNode(mainClassData, "class", "");
  mainNode.dataset.path = mainClass;
  elements.treeRoot.appendChild(mainNode);

  // Expand path
  if (parts.length >= 1) {
    await expandPath(parts, mainNode);
  }
}

async function expandPath(parts, startNode) {
  let currentNode = startNode;
  let path = parts[0];

  for (let i = 1; i < parts.length; i++) {
    const childrenContainer = currentNode.querySelector(
      ":scope > .tree-children"
    );
    const toggleIcon = currentNode.querySelector(
      ":scope > .tree-node-content .toggle-icon"
    );

    if (!childrenContainer || !toggleIcon) break;

    // Expand current node
    toggleIcon.classList.add("expanded");
    childrenContainer.classList.add("expanded");

    // Load children if needed
    if (!state.loadedNodes.has(path)) {
      await loadChildren(
        path,
        i === 1 ? "class" : i === 2 ? "subclass" : "subcategory",
        childrenContainer
      );
    }

    // Find next child
    const nextId = parseInt(parts[i]);
    const children = childrenContainer.querySelectorAll(":scope > .tree-node");
    let found = false;

    for (const child of children) {
      const content = child.querySelector(".tree-node-content");
      const label = content.querySelector(".node-title, .node-ec");
      if (
        label &&
        (label.textContent.includes(`.${nextId}`) ||
          parseInt(child.dataset.path?.split(".").pop()) === nextId)
      ) {
        currentNode = child;
        found = true;
        break;
      }
    }

    if (!found) break;
    path += `.${nextId}`;
  }

  // Scroll to last node
  setTimeout(() => {
    currentNode.scrollIntoView({ behavior: "smooth", block: "center" });
    currentNode.querySelector(".tree-node-content").classList.add("active");
  }, 300);
}

// ============================================
// Expand/Collapse All
// ============================================
async function expandAllVisible() {
  const nodes = document.querySelectorAll(".tree-node");
  for (const node of nodes) {
    const level = node.dataset.level;
    if (level === "enzyme") continue;

    const path = node.dataset.path;
    const toggleIcon = node.querySelector(
      ":scope > .tree-node-content .toggle-icon"
    );
    const childrenContainer = node.querySelector(":scope > .tree-children");

    if (toggleIcon && childrenContainer && !state.expandedNodes.has(path)) {
      state.expandedNodes.add(path);
      toggleIcon.classList.add("expanded");
      childrenContainer.classList.add("expanded");

      if (!state.loadedNodes.has(path)) {
        await loadChildren(path, level, childrenContainer);
      }
    }
  }
}

function collapseAll() {
  const allToggles = document.querySelectorAll(".toggle-icon.expanded");
  const allChildren = document.querySelectorAll(".tree-children.expanded");
  const allActive = document.querySelectorAll(".tree-node-content.active");

  allToggles.forEach((t) => t.classList.remove("expanded"));
  allChildren.forEach((c) => c.classList.remove("expanded"));
  allActive.forEach((a) => a.classList.remove("active"));

  state.expandedNodes.clear();
}

// ============================================
// Event Listeners
// ============================================
elements.searchInput.addEventListener("keydown", (e) => {
  if (e.key === "Enter") {
    handleSearch(e.target.value);
  }
});

elements.expandAll.addEventListener("click", expandAllVisible);
elements.collapseAll.addEventListener("click", collapseAll);
elements.detailClose.addEventListener("click", closeDetailPanel);
elements.detailOverlay.addEventListener("click", closeDetailPanel);

// Keyboard accessibility
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    closeDetailPanel();
  }
});

// ============================================
// Initialize
// ============================================
initializeTree();

// Set initial stats
elements.totalEnzymes.textContent = "~7,000+";
