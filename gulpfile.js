const { src, dest, series, parallel, watch } = require("gulp");
const csso = require("gulp-csso");
const concat = require("gulp-concat");
const gulpsass = require("gulp-sass");
const postcss = require("gulp-postcss");
const autoprefixer = require("autoprefixer");
const babel = require("gulp-babel");
const terser = require("gulp-terser");
const googleWebFonts = require("gulp-google-webfonts");
const hash = require("gulp-hash-filename");
const del = require("del");
const argv = require("yargs").argv;
const rollup = require("rollup");
const resolve = require("rollup-plugin-node-resolve");
const browserSync = require('browser-sync').create();
const sourcemaps = require('gulp-sourcemaps');
const merge = require('merge-stream');
const CONFIGS = [
	require('./gulp.config.public'),
	require('./gulp.config.admin')
];

function sass() {

	let tasks = CONFIGS.map(config => {
		return src( config.sass.src, {
				base: ".",
				allowEmpty: false,
			})
			.pipe( sourcemaps.init() )
			.pipe( gulpsass().on( "error", gulpsass.logError ) )
			.pipe( sourcemaps.write() )
			.pipe( concat("style.css") )
			.pipe( dest( config.buildCSSLocation.dest )) ;
	});

	return merge(tasks);
}

function css() {

	let tasks = CONFIGS.map(config => {
		let stream = src( config.css.src, { sourcemaps: true })
			.pipe( concat( config.cssDeploy.name ) )
			.pipe( postcss([autoprefixer()]) );

		if ( argv.production ) {
			stream = stream.pipe(csso()).pipe(dest( config.cssDeploy.dest )).pipe(hash());
		}
		return stream.pipe(dest( config.cssDeploy.dest, { sourcemaps: '.' } ))
			.pipe(browserSync.stream());
	});

	return merge(tasks);
}

function js() {
	let tasks = CONFIGS.map(config => {
		let stream = src( config.js.src )
			.pipe(concat( config.jsDeploy.name ))
			.pipe(
				babel({
					presets: [
						[
							"@babel/preset-env",
							{
								targets: "last 2 versions",
								modules: false,
							},
						],
					],
				})
			);
		if (argv.production) {
			stream = stream.pipe(terser()).pipe(dest( config.jsDeploy.dest )).pipe(hash());
		}
		return stream.pipe(dest( config.jsDeploy.dest ));
	});
	return merge(tasks);
}

function clean() {
	const paths = [];

	if (argv.photos) {
		paths.push("cache/photos/*", "!cache/photos/.gitignore");
	}

	paths.push("public/assets/css/*");
	paths.push("public/assets/js/*");

	return del(paths);
}

function fonts() {
	let tasks = CONFIGS.map(config => {
		var options = {
			fontsDir: "public/assets/fonts",
			cssDir: config.fonts.src,
			cssFilename: config.fonts.name,
			fontDisplayType: "swap",
			format: "woff2",
			relativePaths: false,
		};
		return src("resources/fonts.list").pipe(googleWebFonts(options)).pipe(dest("./"));
	});

	return merge(tasks);
}

function gulpwatch() {
	let sassSrc = [];
	let cssSrc = [];
	let jsSrc = [];

	CONFIGS.forEach(config => {
		config.sass.src.forEach( path => {
			sassSrc.push(path);
		});

		config.css.src.forEach( path => {
			cssSrc.push(path);
		});

		config.js.src.forEach( path => {
			jsSrc.push(path);
		});
	});

	browserSync.init({
		files: [
			'public/assets/css/**/*',
			'public/assets/js/*'
		],
		proxy: "local.mvctemplate.gr"
	});
	watch(sassSrc, sass);
	watch(cssSrc, css);
	watch(jsSrc, js);
	watch(['./**/*.php', './**/*.html']).on('change', browserSync.reload);
}

exports.sass = sass;
exports.css = css;
exports.js = js;
exports.clean = clean;
exports.fonts = fonts;
// exports.default = series(clean, parallel(series(sass, css), js));
// exports.style = series(sass, css);
exports.watch = gulpwatch;