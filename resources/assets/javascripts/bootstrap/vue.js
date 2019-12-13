import Vue from 'vue';

	window.Vue = Vue;

	/**
	 * The following block of code is used to automatically register your
	 * Vue components. It will recursively scan this directory for the Vue
	 * components and automatically register them with their "basename".
	 *
	 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
	 */

	const files = require.context('../../../vue/components', true, /\.vue$/i);

	files.keys().map(key =>
	{
		Vue.component(
			key
			.split('/')
			.pop()
			.split('.')[0],
			files(key).default
	    )}
	);
