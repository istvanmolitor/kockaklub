import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';

import { menuRegistry } from '@menu';
import { AdminMenuBuilder } from '@admin/lib/AdminMenuBuilder';
import { dashboardRegistry } from '@admin/lib/DashboardRegistry';
import adminRoutes from '@admin/router/index';

import { addressMenuBuilder, addressRoutes } from '@address';
import { currencyMenuBuilder, router as currencyRoutes } from '@currency';
import { customerMenuBuilder, customerRoutes } from '@customer';
import { languageMenuBuilder, router as languageRoutes } from '@language';
import { mediaMenuBuilder, router as mediaRoutes } from '@media';
import { orderMenuBuilder, orderDashboardBuilder, router as orderRoutes } from '@order';
import { productMenuBuilder, router as productRoutes } from '@product';
import { settingMenuBuilder, router as settingRoutes } from '@setting';
import { stockMenuBuilder, router as stockRoutes } from '@stock';
import { userMenuBuilder, userDashboardBuilder, router as userRoutes } from '@user';
import { authGuard } from '@user/router/guards';

import App from './App.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        ...adminRoutes,
        ...userRoutes,
        ...addressRoutes,
        ...currencyRoutes,
        ...customerRoutes,
        ...languageRoutes,
        ...mediaRoutes,
        ...orderRoutes,
        ...productRoutes,
        ...settingRoutes,
        ...stockRoutes,
    ],
});

router.beforeEach(authGuard);

menuRegistry.register(new AdminMenuBuilder());
menuRegistry.register(addressMenuBuilder);
menuRegistry.register(currencyMenuBuilder);
menuRegistry.register(customerMenuBuilder);
menuRegistry.register(languageMenuBuilder);
menuRegistry.register(mediaMenuBuilder);
menuRegistry.register(orderMenuBuilder);
menuRegistry.register(productMenuBuilder);
menuRegistry.register(settingMenuBuilder);
menuRegistry.register(stockMenuBuilder);
menuRegistry.register(userMenuBuilder);

dashboardRegistry.register(orderDashboardBuilder);
dashboardRegistry.register(userDashboardBuilder);

createApp(App).use(router).mount('#app');
