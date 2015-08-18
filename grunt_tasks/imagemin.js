module.exports = {
    target: {
        files: [{
            expand: true,
            cwd: 'www/assets/images/',
            src: ['**/*.{png,jpg,gif}'],
            dest: 'www/assets/images/'
          }]
    }
};