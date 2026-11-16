import '@mdi/font/css/materialdesignicons.css';
import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import { en, ru } from 'vuetify/locale';

export default createVuetify({
    locale: {
        locale: 'ru',
        fallback: 'en',
        messages: { ru, en },
    },
    theme: {
        defaultTheme: 'system',
    },
    defaults: {
        VTextField: { variant: 'outlined', density: 'comfortable' },
        VBtn: { rounded: 'lg' },
        VCard: { rounded: 'lg' },
    },
});
