<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="mb-6">{{ __("Opciones") }}</h1>
                    <div x-data="optionsEmailPdf()" class="grid gap-2 ">
                        <!-- Modal de Carga -->
                        <div x-show="loading" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50">
                            <div class="p-6 rounded-lg max-w-sm w-full text-center">
                                <div class="spinner-border animate-spin border-4 border-t-4 border-gray-300 border-t-gray-600 rounded-full h-16 w-16 mx-auto"></div>
                                <p class="mt-4 text-lg text-white">Cargando, por favor espere...</p>
                            </div>
                        </div>
                        <!--  Conexion  -->
                        <div class="mt-3 w-full">
                            <button type="button" @click="options(0)" class="w-full px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-100 rounded hover:bg-gray-400 dark:hover:bg-gray-500">
                                Prueba de conexion
                            </button> 
                            <div x-show="status_0" class="mt-2" x-text="message_0" :class="style_0=='success' ? 'text-green-600' : 'text-red-600'"></div>   
                        </div>
                        <!--  Informacion de correos  -->
                        <div class="mt-3 w-full">
                            <button type="button" @click="options(1)" class="w-full px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-100 rounded hover:bg-gray-400 dark:hover:bg-gray-500">
                                Obtener informacion de correo
                            </button> 
                            <div x-show="status_1" class="mt-2" x-text="message_1" :class="style_1=='success' ? 'text-green-600' : 'text-red-600'"></div>
                        </div>
                        <!--  Informacion de correos y pdf -->
                        <div class="mt-3 w-full">
                            <button type="button" @click="options(2)" class="w-full px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-100 rounded hover:bg-gray-400 dark:hover:bg-gray-500">
                                Obtener informacion de cartas
                            </button> 
                            <div x-show="status_2" class="mt-2" x-text="message_2" :class="style_2=='success' ? 'text-green-600' : 'text-red-600'"></div>
                        </div>
                        <!--  Borrado de archivos temporales  -->
                        <div class="mt-3 w-full">
                            <button type="button" @click="options(3)" class="w-full px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-100 rounded hover:bg-gray-400 dark:hover:bg-gray-500">
                                Eliminar archivos
                            </button> 
                            <div x-show="status_3" class="mt-2" x-text="message_3" :class="style_3=='success' ? 'text-green-600' : 'text-red-600'"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<script>
    function optionsEmailPdf(){
        return {
            token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            url: [
                '/test-connection',
                '/email-info',
                '/email-pdf-letters',
                '/delete-pdf'
            ],
            message_0: '',
            message_1: '',
            message_2: '',
            message_3: '',
            status_0: false,
            status_1: false,
            status_2: false,
            status_3: false,
            style_0: '',
            style_1: '',
            style_2: '',
            style_3: '',
            loading: false,

            options(opt){
                for (let i = 0; i < this.url.length; i++) {
                    this['message_' + i] = '';
                    this['style_' + i] = '';
                    this['status_' + i] = false;
                }

                this.loading = true;
                tmp_url = this.url[opt];
                tmp_status = 'status_'+opt;
                tmp_msg =  'message_'+opt;
                tmp_style = 'style_'+opt;
                    
                axios.get(tmp_url, {
                    headers: {'X-CSRF-TOKEN': this.token}
                })
                .then(response => {
                    this[tmp_status] = true;
                    this[tmp_style] = 'success';
                    this[tmp_msg] = response.data.message;
                })
                .catch(error => {
                    this[tmp_status] = true;
                    this[tmp_style] = 'error';
                    this[tmp_msg] = 'Error de red';
                })
                .finally(() => {
                    this.loading = false; 
                });
            },
        }
    }
</script>