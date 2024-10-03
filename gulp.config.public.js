// gulp.config.public.js
module.exports = {
    app: {
        baseName: 'public'
    },
    sass: {
        src: [
            'resources/public/**/*.scss'
        ]
    },
    buildCSSLocation: {
        dest: 'resources/public/css'
    },
    css: {
        src: [
            'node_modules/@fortawesome/fontawesome-free/css/all.css',
            'resources/public/css/*.css'
        ]
    },
    cssDeploy: {
        name: 'style-public.css',
        dest: 'public/assets/css'
    },
    js: {
        src: [
            "node_modules/jquery/dist/jquery.min.js",
            "node_modules/bootstrap/dist/js/bootstrap.bundle.min.js",
            "resources\/public\/js\/*.js"
        ]
    },
    jsDeploy: {
        name: 'script-public.js',
        dest: 'public/assets/js'
    },
    fonts: {
        src: ['resources/public/css']
    },
    fontsDeploy: {
        name: 'fonts-public.css'
    }
};