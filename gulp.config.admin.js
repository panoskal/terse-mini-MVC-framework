// gulp.config.admin.js
module.exports = {
    app: {
        baseName: 'admin'
    },
    sass: {
        src: ['resources/admin/**/*.scss']
    },
    buildCSSLocation: {
        dest: 'resources/admin/css'
    },
    css: {
        src: [
            'node_modules/@fortawesome/fontawesome-free/css/regular.css',
            'node_modules/icheck-bootstrap/icheck-bootstrap.css',
            'node_modules/select2/dist/css/select2.css',
            'node_modules/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.css',
            'resources/admin/css/*.css'
        ]
    },
    cssDeploy: {
        name: 'style-admin.css',
        dest: 'public/assets/css'
    },
    js: {
        src: [
            "node_modules/jquery/dist/jquery.min.js",
            "node_modules/bootstrap/dist/js/bootstrap.bundle.min.js",
            "node_modules/select2/dist/js/select2.full.min.js",
            "node_modules/admin-lte/dist/js/adminlte.min.js",
            'resources/admin/js/*.js'
        ]
    },
    jsDeploy: {
        name: 'script-admin.js',
        dest: 'public/assets/js'
    },
    fonts: {
        src: ['resources/admin/css']
    },
    fontsDeploy: {
        name: 'fonts-admin.css',
    }
};