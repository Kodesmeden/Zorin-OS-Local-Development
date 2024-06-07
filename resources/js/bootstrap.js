import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Feather Icons
import feather from 'feather-icons';
feather.replace();

// Google Fonts Roboto
import 'typeface-montserrat';