let canvas = document.getElementsByClassName("smiles_drawer");
let h = $ts("@drawer-h") ?? "250";
let options = {
  width: 400,
  height: parseInt(h)
};
// Initialize the drawer to draw to canvas
let smilesDrawer = new SmilesDrawer.Drawer(options);
// Alternatively, initialize the SVG drawer:
// let svgDrawer = new SmilesDrawer.SvgDrawer(options);

for (let element of canvas) {
  let smiles = element.getAttribute("data");

  if (smiles || smiles.length > 0) {
    // Clean the input (remove unrecognized characters, such as spaces and tabs) and parse it
    SmilesDrawer.parse(
      smiles,
      function (tree) {
        // Draw to the canvas
        smilesDrawer.draw(tree, element, "light", false);
        // Alternatively, draw to SVG:
        // svgDrawer.draw(tree, 'output-svg', 'dark', false);
      },
      function (err) {
        console.log(err);
      }
    );
  }
}
