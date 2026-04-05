import './bootstrap';
import { createApp } from 'vue';
import App from './spa/App.vue';
import { createSpaRouter } from './spa/router';

const app = createApp(App);

app.use(createSpaRouter());
app.mount('#app');
