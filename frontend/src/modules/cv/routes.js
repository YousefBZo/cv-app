export default [
  { path: '/cv', name: 'CV', component: () => import('./views/CVView.vue') },
  { path: '/profile', name: 'Profile', component: () => import('./views/ProfileView.vue') },
  { path: '/education', name: 'Education', component: () => import('./views/EducationView.vue') },
  { path: '/experience', name: 'Experience', component: () => import('./views/ExperienceView.vue') },
  { path: '/project', name: 'Project', component: () => import('./views/ProjectView.vue') },
  { path: '/skill', name: 'Skill', component: () => import('./views/SkillView.vue') },
  { path: '/language', name: 'Language', component: () => import('./views/LanguageView.vue') },
  { path: '/certification', name: 'Certification', component: () => import('./views/CertificationView.vue') },
  { path: '/volunteer', name: 'VolunteerExperience', component: () => import('./views/VolunteerView.vue') },
]
