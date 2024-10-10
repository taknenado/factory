<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const form = reactive({
    name: null,
    suppliers_id: null,
    price_per_unit: null,
});

let isOpenModal = ref(false);
let messageResponse = ref('');
const suppliers = ref([]);
const isLoading = ref(true); // Добавляем состояние загрузки

function closemessageResponse() {
    isOpenModal.value = false;
}

const nomenclature = reactive({});

function getNomenclature() {
    axios({
        method: 'get',
        url: '/get-nomenclature',
    }).then((response) => {
        nomenclature.value = response.data.nomenclatures;
        isLoading.value = false; // Отключаем прелоадер после загрузки данных
    });
}

function getSuppliers() {
    axios({
        method: 'get',
        url: '/get-suppliers',
    }).then((response) => {
        suppliers.value = response.data.suppliers;
        isLoading.value = false; // Отключаем прелоадер после загрузки данных
    });
}

onMounted(() => {
    getNomenclature();
    getSuppliers();
});

function deleteNomenclature(ids) {
    let a = confirm('Вы действительно хотите удалить запись?');
    if (a == true) {
        axios.post('/delete-nomenclature', {
            nomenclature_id: ids,
        }).then((response) => {
            isOpenModal.value = true;
            messageResponse.value = response.data.status;
            getNomenclature();
            setTimeout(closemessageResponse, 2000);
        });
    }
}

function updateTable(mes) {
    isOpenModal.value = true;
    messageResponse.value = mes;
    getNomenclature();
    form.name = '';
    form.suppliers_id = '';
    form.price_per_unit = '';
    setTimeout(closemessageResponse, 2000);
}

function responseNomenclature() {
    axios({
        method: 'post',
        url: '/add-nomenclature',
        data: {
            csrf: csrf,
            name: form.name,
            supplier_id: form.suppliers_id,
            price_per_unit: form.price_per_unit,
        },
    }).then((response) => {
        updateTable(response.data.status);
    });
}
</script>

<template>
    <AppLayout title="Nomenclature">
        <div class="modalMessage" v-if="isOpenModal">{{ messageResponse }}</div>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Номенклатура
            </h2>
        </template>

        <div class="py-12" v-if="!isLoading">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <h3 class="text-lg font-medium mb-4">Добавить новую номенклатуру</h3>
                    <form>
                        <input type="hidden" name="_token" :value="csrf">

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Имя</label>
                            <input type="text" id="name" v-model="form.name" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                        <div class="mb-4">
                            <label for="supplier" class="block text-sm font-medium text-gray-700">Поставщик</label>
                            <select id="supplier" v-model="form.suppliers_id" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500">
                                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                    {{ supplier.name }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="price_per_unit" class="block text-sm font-medium text-gray-700">Цена за единицу</label>
                            <input type="number" id="price_per_unit" v-model="form.price_per_unit" step="0.01" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                        <button type="submit" @click.prevent="responseNomenclature"
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Добавить номенклатуру
                        </button>
                    </form>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <h3 class="text-lg font-medium mb-4">Список номенклатуры</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Имя
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Поставщик
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Цена за единицу
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Общее количество
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Общая цена
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Действие
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in nomenclature.value" :key="item.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ item.name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ item.supplier.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ item.price_per_unit }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ item.total_quantity }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ item.total_price }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a @click="deleteNomenclature(item.id)" :data="item.id" class="ml-2 text-red-600 hover:text-red-900">Delete</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="preloader">
            <div class="loader"></div>
        </div>
    </AppLayout>
</template>

<style>
.modalMessage {
    position: fixed;
    top: 10%;
    border: 1px solid #ccc;
    box-shadow: 0px 0px 20px #444;
    background-color: rgb(0, 95, 13);
    padding: 20px 40px;
    color: #ccc;
}
a {
    cursor: pointer;
}
.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(5px);
}
.loader {
    border: 16px solid #f3f3f3;
    border-radius: 50%;
    border-top: 16px solid #3498db;
    width: 120px;
    height: 120px;
    animation: spin 2s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>