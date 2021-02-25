module.exports = {
	deploy: {
		production: {
			user: 'appolonia',
			host: '165.227.213.118',
			ref: 'origin/main',
			repo: 'git@github.com:good-idea/hipline-cms',
			path: '/home/appolonia/panel.myhipline.com',
			'post-deploy': 'echo "CMS has been updated"',
		},
	},
}
