import axios from 'axios';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Axios reads Laravel's XSRF-TOKEN cookie for each same-origin request.
// A fixed header would become stale when authentication rotates the token.

import './echo.ts';
