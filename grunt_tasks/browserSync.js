module.exports = {
    dev: {
        bsFiles: {
            src : [
                'www/assets/styles/dist/*.css',
                'www/assets/scripts/dist/*.js',
                'www/assets/images/**/*',
                '**/*.php'
            ]
        },
        options: {
            proxy: "localhost",
            port: 3000,
            watchTask: true,
            notify: false
        }
    }
};
