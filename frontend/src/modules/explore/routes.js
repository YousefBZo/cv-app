export default [
  {
    path: '/u/:slug',
    name: 'PublicCV',
    component: () => import('./views/PublicCVView.vue'),
    meta: { public: true },
  },
]
