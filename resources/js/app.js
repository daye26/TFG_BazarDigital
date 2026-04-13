import './bootstrap';
import './alpine';

import { initCartToastFromSession } from './cart/toast';
import { initQuantitySteppers } from './cart/quantity-stepper';
import { initAjaxCartForms, initAjaxCartUpdateForms } from './cart/forms';
import { initGlobalSearch } from './search/global-search';
import { initAdminStatsCharts } from './admin/stats-charts';

initCartToastFromSession();
initQuantitySteppers();
initAjaxCartForms();
initAjaxCartUpdateForms();
initGlobalSearch();
initAdminStatsCharts();
