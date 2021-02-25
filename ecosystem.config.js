module.exports = {
	apps: [
		{
			name: 'myhipline.com',
			script: 'yarn serve:prod',
			autorestart: true,
			watch: false,
			max_memory_restart: '300M',
			env: {
				ENV: 'production',
				CMS_PORT: '80',
				CMS_HOST: 'panel.myhipline.com',
			},
		},
	],
	deploy: {
		production: {
			user: 'appolonia',
			host: '165.227.213.118',
			ref: 'origin/main',
			repo: 'git@github.com:good-idea/hipline-express',
			path: '/home/appolonia/myhipline.com',
			'post-deploy': 'yarn install && yarn build',
		},
	},
}
