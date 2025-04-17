const chokidar = require("chokidar");
const { exec } = require("child_process");
const path = require("path");

console.log("👀 Vigilando carpeta de imágenes en: src/assets/images/originals");

const watcher = chokidar.watch("src/assets/images/originals/**/*.{png,jpg,jpeg}", {
  persistent: true,
  ignoreInitial: true,
  usePolling: true,
  interval: 500,
  binaryInterval: 300,
  awaitWriteFinish: {
    stabilityThreshold: 500,
    pollInterval: 100
  }
});

watcher.on("add", optimize);
watcher.on("change", optimize);

function optimize(filePath) {
  const fileName = path.basename(filePath);
  console.log(`🖼️ Nueva imagen o modificación detectada: ${fileName}. Optimizando...`);

  exec("node scripts/convert-images.js", (error, stdout, stderr) => {
    if (error) {
      console.error(`❌ Error: ${error.message}`);
      return;
    }
    if (stderr) {
      console.error(`⚠️  Stderr: ${stderr}`);
      return;
    }
    console.log(`✅ Conversión completada:\n${stdout}`);
  });
}
