module.exports = {
	dev: {
		bsFiles: {
			src : [
				 'assets/styles/dist/*.css'
				,'assets/scripts/dist/*.js'
				,'assets/images/**/*'
				,'**/*.php'
			]
		},
		options: {
			proxy: "localhost",
			port: 3000,
			watchTask: true,
			notify: false
		}
	}
}
