import { createRouter, createWebHistory } from 'vue-router'
import AssignmentList from '../components/AssignmentList.vue'
import QuizEditor from '../components/QuizEditor.vue'

const routes = [
  { path: '/', name: 'assignment-list', component: AssignmentList },
  { path: '/assignments/create', name: 'assignment-create', component: QuizEditor, props: { isEdit: false } },
  { path: '/assignments/:id/edit', name: 'assignment-edit', component: QuizEditor, props: route => ({ assignmentId: Number(route.params.id), isEdit: true }) }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
