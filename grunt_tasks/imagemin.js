module.exports = {
    target: {
        files: [{
            expand: true,
            cwd: 'assets/images/',
            src: ['**/*.{png,jpg,gif}'],
            dest: 'assets/images/'
          }]
    }
}