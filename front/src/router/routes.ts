// @ts-ignore
import { RouteRecordRaw } from "vue-router"; // @ts-ignore
// {{ laravue-insert:import }}

const routes: RouteRecordRaw[] = [
  {
    path: "/", // @ts-ignore
    component: () => import("layouts/MainLayout.vue"),
    children: [
      {
        path: "",
        name: "home", // @ts-ignore
        component: () => import("src/pages/HomePage.vue"),
      },
      // {{ laravue-insert:route }}
    ],
  },
  {
    path: "/:catchAll(.*)*", // @ts-ignore
    component: () => import("pages/ErrorNotFound.vue"),
  },
];

export default routes;
