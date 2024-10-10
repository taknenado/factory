<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

import { ref, reactive } from 'vue'
//import { useForm } from '@inertiajs/vue3'
import axios from 'axios'


let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

const form = reactive({
    supplierName: null,
    address: null,
    supplierComments: null,
    phoneNumber: null
})

// если редактируем
let isEdit = ref(false);
let isEditId = ref(0);
// открыто ли модальное окно 
let isOpenModal = ref(false);
// сообщение от сервера
let messageResponse = ref('');
let messageResponseColor = ref('');
// загруженны ли данные с сервера
let isLoaded = ref(false);

function closemessageResponse(){
    isOpenModal.value = false;
    messageResponse.value = '';
    messageResponseColor.value = '';
}

const suppliers = reactive({})
function  getSuppliers(){
    axios({
            method: 'get',
            url: '/get-suppliers',
        }).then((response) => {
            suppliers.value = response.data.suppliers;
            isLoaded.value = true;
    })
}
getSuppliers();


function deleteSuppliers(ids){

    let a = confirm('Вы действительно хотите удалить запись?');
    if (a == true) {
        axios.post('/delete-suppliers', {
            suppliers_id: ids
        }
        ).then((response) => {
            isOpenModal.value = true;
            messageResponse.value = response.data.status
            getSuppliers();
            setTimeout(closemessageResponse, 2000);
        })
    }

}

function updateSuppliers(ids){
    let b = suppliers.value.find((el) => el.id == ids);
    form.supplierName = b.name;
    form.supplierComments = b.comments;
    form.address = b.address;
    form.phoneNumber = b.phone;
    isEdit = true;
    isEditId = ids;
}

function clearSuppliers(){
    form.supplierName = '';
    form.supplierComments = '';
    form.address = '';
    form.phoneNumber = null;
}

function updateTable(mes, isok){
    isEdit = false;
    isEditId = 0;
    isOpenModal.value = true;
    messageResponse.value = mes;
    if (isok) {
        messageResponseColor.value = 'mgreen';
    } else {
        messageResponseColor.value = 'mred';
    }
    
    getSuppliers();
    setTimeout(closemessageResponse, 2000);
}


function responseSuppliers() {
    axios({
            method: 'post',
            url: '/add-suppliers',
            data: {
                csrf: csrf,
                supplierName: form.supplierName,
                address: form.address,
                supplierComments: form.supplierComments,
                phoneNumber: form.phoneNumber
        }
        }).then((response) => {
            if (response.data.isOk){
                clearSuppliers() 
                updateTable(response.data.status, response.data.isOk)
            } else {
                isOpenModal.value = true;
                messageResponse.value = response.data.status;
                messageResponseColor.value = 'mred';
                setTimeout(closemessageResponse, 2000);
            }
            
        })
}

function updateSuppliersToServ() {
    axios({
            method: 'post',
            url: '/update-suppliers',
            data: {
                csrf: csrf,
                supplierId: isEditId,
                supplierName: form.supplierName,
                address: form.address,
                supplierComments: form.supplierComments,
                phoneNumber: form.phoneNumber
        }
        }).then((response) => {
            if (response.data.isOk){
                clearSuppliers() 
                updateTable(response.data.status, response.data.isOk)
            } else {
                isOpenModal.value = true;
                messageResponse.value = response.data.status;
                messageResponseColor.value = 'mred';
                setTimeout(closemessageResponse, 2000);
            }
           
        })
}

</script>

<template>
    <AppLayout title="Suppliers">
        <div v-if = "!isLoaded"  class="preload">
            <div class="preload2"></div>
        </div>
        <div class="modalMessage" :class="messageResponseColor" v-if="isOpenModal">{{ messageResponse }}</div>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Поставщики
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    
                    
                  
                    <h3 class="text-lg font-medium mb-4">Добавить нового поставщика</h3>
                    <form>
                        <input type="hidden" name="_token" :value="csrf">
                        <div class="mb-4">
                            <label for="supplierName" class="block text-sm font-medium text-gray-700">Имя поставщика</label>
                            <input type="text" id="supplierName" v-model="form.supplierName" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                        <div class="mb-4">
                            <label for="phoneNumber" class="block text-sm font-medium text-gray-700">Номер телефона</label>
                            <input type="tel" id="phoneNumber" v-model="form.phoneNumber" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-700">Адрес</label>
                            <textarea id="address" v-model="form.address" required
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>


                        <div class="mb-4">
                            <label for="supplierComments" class="block text-sm font-medium text-gray-700">Комментарий</label>
                            <input type="text" id="supplierComments" v-model="form.supplierComments" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                        
                        <button v-if="!isEdit" type="submit" @click.prevent ="responseSuppliers()"
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Добавить поставщика
                        </button>
                        <button v-else type="submit" @click.prevent="updateSuppliersToServ()"
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Изменить
                        </button>
                        
                    </form>
                    
                    

                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <h3 class="text-lg font-medium mb-4">Список поставщиков</h3>
                    



<table v-if = "isLoaded" class="min-w-full divide-y divide-gray-200 overflow-x-auto">
    <thead class="bg-gray-50">
        <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Имя
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Адрес
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Номер телефона
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Комментарий
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Действия
            </th>
        </tr>
    </thead>
    
        
 
    <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="item in suppliers.value" :key="item.id">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full" src="https://i.pravatar.cc/150?img=1" alt="">
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">
                            {{ item.name }}
                        </div>
                        <div class="text-sm text-gray-500">
                            jane.cooper@example.com
                        </div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ item.address }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ item.phone }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ item.comments }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap  text-sm font-medium">
                <a @click="updateSuppliers(item.id)" :data="item.id" class="text-indigo-600 hover:text-indigo-900">Редактировать</a>
                <a @click="deleteSuppliers(item.id)" :data="item.id" class="ml-2 text-red-600 hover:text-red-900">Удалить</a>
            </td>
        </tr>
        </tbody>
        </table>   

                </div>
            </div>
        </div>
    </AppLayout>
</template>
<style>

</style>