const { src, dest } = require('gulp');
const { series } = require('gulp');
const clean = require('gulp-clean');
const zip = require('gulp-zip').default;
const fs = require('fs/promises');

const config = {
  version: '1.0.0',
  plugin_name: 'growfund',
  plugin_path: './wordpress/wp-content/plugins/growfund',
  apps_path: './apps',
  build_path: './build',
};

const tasks = {
  plugin: {
    src: [
      `${config.plugin_path}/**/*.{php,js,css,json,md,txt,pot,po,mo,jpg,jpeg,png,svg,webp,ttf,html,woff,woff2}`,
    ],
    dest: config.build_path,
  },
};

async function detectPluginVersion() {
  const manifest = await fs.readFile(`${config.plugin_path}/growfund.php`, 'utf-8');
  const versionMatch = manifest.match(/\*\s*Version:\s*([0-9.]+)/);
  if (versionMatch) {
    config.version = versionMatch[1];
  }
}

function makePackageName() {
  return `${config.plugin_name}-v${config.version}.zip`;
}

function cleanExistingBuild() {
  return src(config.build_path, { read: false, allowEmpty: true }).pipe(clean());
}

function copyPluginFiles() {
  return src(tasks.plugin.src, { dot: true, allowEmpty: true, encoding: false }).pipe(
    dest(tasks.plugin.dest),
  );
}

function makeZip() {
  return src([`${config.build_path}/**`], { dot: true, allowEmpty: true, encoding: false })
    .pipe(zip(makePackageName()))
    .pipe(dest(config.build_path));
}

exports.default = series(detectPluginVersion, cleanExistingBuild, copyPluginFiles);
exports.zip = series(detectPluginVersion, cleanExistingBuild, copyPluginFiles, makeZip);
