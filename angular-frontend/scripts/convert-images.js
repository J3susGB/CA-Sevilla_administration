const sharp = require("sharp");
const fs = require("fs");
const path = require("path");

const inputFolder = "src/assets/images/originals";
const outputFolder = "src/assets/optimized";

if (!fs.existsSync(outputFolder)) {
  fs.mkdirSync(outputFolder, { recursive: true });
}

fs.readdirSync(inputFolder).forEach(file => {
  const filePath = path.join(inputFolder, file);
  const ext = path.extname(file).toLowerCase();
  const baseName = path.parse(file).name;

  if (![".png", ".jpg", ".jpeg"].includes(ext)) return;

  const formats = [
    { ext: ".webp", format: "webp" },
    { ext: ".avif", format: "avif" },
    { ext: ext, format: null } // copia original como está (png o jpg)
  ];

  formats.forEach(({ ext: outExt, format }) => {
    const outPath = path.join(outputFolder, `${baseName}${outExt}`);

    const transformer = format ? sharp(filePath).toFormat(format) : sharp(filePath);

    transformer
      .toFile(outPath)
      .then(() => {
        const label = format ? `${format.toUpperCase()}` : "ORIGINAL";
        console.log(`✅ ${label} generado: ${baseName}${outExt}`);
      })
      .catch(err => {
        console.error(`❌ Error generando ${baseName}${outExt}:`, err.message);
      });
  });
});
