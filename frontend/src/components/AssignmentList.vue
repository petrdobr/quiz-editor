<template>
  <div class="container mt-4">
    <h2 class="mb-4">Мои задания</h2>

    <!-- Кнопка создать -->
    <div class="mb-3">
      <button class="btn btn-success" @click="createNew">Создать новое задание</button>
    </div>

    <!-- Ошибки -->
    <div v-if="error" class="alert alert-danger">Ошибка: {{ error }}</div>

    <!-- Таблица -->
    <table class="table table-bordered" v-if="assignments.length">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Название</th>
          <th>Тип</th>
          <th>Обновлено</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="a in assignments" :key="a.id">
          <td>{{ a.id }}</td>
          <td>{{ a.title }}</td>
          <td>{{ a.type }}</td>
          <td>{{ new Date(a.updatedAt).toLocaleString() }}</td>
          <td>
            <button class="btn btn-sm btn-primary me-2" @click="editAssignment(a.id)">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-danger" @click="deleteAssignment(a.id)">
              <i class="bi bi-trash"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-else class="text-muted">Заданий пока нет.</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { API_BASE_URL } from '../config/api'

const assignments = ref([])
const error = ref(null)
const router = useRouter()

async function fetchAssignments() {
  try {
    const res = await fetch(`${API_BASE_URL}/assignments`)
    if (!res.ok) throw new Error('Ошибка загрузки заданий')
    assignments.value = await res.json()
  } catch (e) {
    error.value = e.message
  }
}

async function deleteAssignment(id) {
  if (!confirm('Удалить задание?')) return
  try {
    const res = await fetch(`${API_BASE_URL}/assignments/${id}`, { method: 'DELETE' })
    if (!res.ok) throw new Error('Ошибка удаления')
    assignments.value = assignments.value.filter(a => a.id !== id)
  } catch (e) {
    error.value = e.message
  }
}

function createNew() {
  router.push({ name: 'assignment-create' })
}

function editAssignment(id) {
  router.push({ name: 'assignment-edit', params: { id } })
}

onMounted(fetchAssignments)
</script>
