@extends('layout.admin')
@section('title laporan', 'Manajemen Pegawai')

@section('content')
    <div x-data="{ showModalUser: false, deleteId: null }"
        class="relative overflow-x-auto shadow-md sm:rounded-lg mx-28 my-10">
        <div class="flex items-center justify-between" style="background:#EEF0F4">
            <!-- tambah pegawai -->
            <a href="{{ route('pegawai.create') }}" class="flex items-center group">
                <button title="Add New" class="cursor-pointer outline-none group-hover:rotate-90 duration-300 ml-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25px" height="25px" viewBox="0 0 24 24"
                        class="stroke-blue-600 fill-none group-active:stroke-blue-600 group-active:duration-0 duration-300">
                        <path d="M12 22C17.5 22 22 17.5 22 12C22 6.5 17.5 2 12 2C6.5 2 2 6.5 2 12C2 17.5 6.5 22 12 22Z"
                            stroke-width="1.5"></path>
                        <path d="M8 12H16" stroke-width="1.5"></path>
                        <path d="M12 16V8" stroke-width="1.5"></path>
                    </svg>
                </button>
                <p class="text-blue-600 group-hover:underline px-2" style="font-weight:bold; font-size:13px">Tambah</p>
            </a>
            <!-- search pegawai -->
            <div class="relative">
                <label for="search" class="mb-2 text-sm font-medium text-gray-900 sr-only">Search</label>
                <div class="relative py-3">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                        </svg>
                    </div>
                    <input type="text" id="search"
                        class="mr-3 block w-72 h-5 p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50"
                        placeholder="Search..." />
                </div>
                <div id="result" class="mt-4 text-sm text-gray-700"></div>
            </div>
        </div>
        <table class="w-full text-sm text-left rtl:text-right text-gray-500">
            <thead class="text-xs text-white uppercase bg-[#324150]">
                <tr>
                    <th scope="col" class="px-3 py-3">
                        Name
                    </th>
                    <th scope="col" class="px-3 py-3">
                        Email
                    </th>
                    <th scope="col" class="px-3 py-3">
                        Password
                    </th>
                    <th scope="col" class="px-3 py-3">
                    </th>
                </tr>
            </thead>
            <tbody id="user-body">
            </tbody>
        </table>
        <!-- pagination -->
        <div class="flex justify-between bg-[#E8E8E8]">
            <div class="flex items-center my-2 ml-4">
                <label for="per_page" class="block text-sm font-medium text-gray-700 pr-2">Items per page:</label>
                <select id="per_page"
                    class="mt-1 block cursor-pointer text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="15">15</option>
                    <option value="20">20</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
            <div id="pagination" class="flex gap-2 m-4 flex-wrap"></div>
        </div>
        <!-- Modal Konfirmasi -->
        <div x-show="showModalUser" x-cloak
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                <h2 class="text-lg font-semibold text-gray-800">Konfirmasi Hapus</h2>
                <p class="mt-2 text-sm text-gray-600">Apakah Anda yakin ingin menghapus data ini?
                </p>
                <div class="mt-4 flex justify-end space-x-2">
                    <button @click="showModalUser = false"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100">Batal</button>
                    <button @click="deleteUser(deleteId)"
                        class="px-4 py-2 text-sm font-semibold text-white bg-red-700 rounded-lg hover:bg-red-800">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script>
        let currentPerPage = 10; // default
        let currentPageGlobal = 1;

        async function fetchUser(page = 1, perPage = 10, search = '') {
            //cek role akses
            const userP = JSON.parse(sessionStorage.getItem('user') || '{}')

            if (userP.role == 'employee') {
                window.location.href = '/dashboard';
            }


            const token = sessionStorage.getItem('auth_token');
            const url = new URL(`https://backend-alganis-production.up.railway.app/api/pegawai`, window.location.origin);

            url.searchParams.append('page', page);
            url.searchParams.append('per_page', perPage);
            if (search) {
                url.searchParams.append('search', search); // contoh: ?search=naufal
            }

            const response = await fetch(url.toString(), {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            if (!response.ok) {
                if (response.status === 401) {
                    sessionStorage.removeItem('auth_token');
                    window.location.href = '/';
                }
                throw new Error('Failed to fetch data');
            }

            const data = await response.json();

            const user = data.data.data;
            const tbody = document.getElementById('user-body');

            tbody.innerHTML = user.map(item => `
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <th scope="row" class="px-3 py-1 font-medium text-gray-900 whitespace-nowrap">
                                            ${item.name}
                                        </th>
                                        <td class="px-3 py-1">
                                            ${item.email}
                                        </td>
                                        <td class="px-3 py-1">
                                            ********
                                        </td>
                                        <td class="flex items-center px-3 py-1 justify-end">
                                            <a href="/pegawai/edit/${item.user_id}"
                                                class="border-2 border-[#A3A3A3] rounded p-1 hover:bg-green-100 my-3 relative group">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                    <path fill="#B3BE1A" fill-rule="evenodd"
                                                        d="M17.204 10.793L19 9c.545-.545.818-.818.934-1.112a2 2 0 0 0 0-1.773C19.818 5.818 19.545 5.545 19 5s-.818-.818-1.112-.934a2 2 0 0 0-1.773 0c-.294.143-.537.419-1.112.934l-1.819 1.819a10.9 10.9 0 0 0 4.023 3.977m-5.477-2.523l-3.87 3.87c-.423.423-.338.338-.778.9c-.14.23-.199.555-.313 1.145l-.313 3.077c-.03.332-.1.498-.005.593s.23.031.593-.005l3.077-.313c.59-.117.885-.173 1.143-.313s.473-.352.898-.777l3.89-3.89a12.9 12.9 0 0 1-4.02-3.98"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span
                                                    class="absolute -top-8 left-1/2 -translate-x-1/2 hidden group-hover:block bg-[#B6BE1A] text-white text-xs rounded py-1 px-2 shadow-md">
                                                    Edit
                                                </span>
                                            </a>
                                            <!-- Tombol Delete -->
                                            <div class="relative group">
                                                <button @click="showModalUser = true; deleteId = ${item.user_id}"
                                                    class="bg-white border-2 border-[#A3A3A3] rounded p-1 hover:bg-red-100 mx-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                        <g fill="none">
                                                            <path fill="#C50505" fill-rule="evenodd"
                                                                d="M21 6H3v3a2 2 0 0 1 2 2v4c0 2.828 0 4.243.879 5.121C6.757 21 8.172 21 11 21h2c2.829 0 4.243 0 5.121-.879c.88-.878.88-2.293.88-5.121v-4a2 2 0 0 1 2-2zm-10.5 5a1 1 0 0 0-2 0v5a1 1 0 1 0 2 0zm5 0a1 1 0 0 0-2 0v5a1 1 0 1 0 2 0z"
                                                                clip-rule="evenodd" />
                                                            <path stroke="#C50505" stroke-linecap="round" stroke-width="2"
                                                                d="M10.068 3.37c.114-.106.365-.2.715-.267A6.7 6.7 0 0 1 12 3c.44 0 .868.036 1.217.103s.6.161.715.268" />
                                                        </g>
                                                    </svg>
                                                </button>
                                                <span
                                                    class="absolute -top-8 left-1/2 -translate-x-1/2 hidden group-hover:block bg-red-800 text-white text-xs rounded py-1 px-2 shadow-md">
                                                    Hapus
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                    `).join('');
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';
            const currentPage = data.pagination.current_page;
            currentPageGlobal = currentPage;
            const lastPage = data.pagination.last_page;

            // Tombol Prev
            if (currentPage > 1) {
                const prevLink = document.createElement('a');
                prevLink.href = '#';
                prevLink.textContent = '< Prev';
                prevLink.className = 'px-2 py-1 mx-1 text-[#161D6F] hover:text-blue-500 duration-300';
                prevLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    fetchUser(currentPage - 1, currentPerPage);
                });
                pagination.appendChild(prevLink);
            }

            // Dropdown untuk memilih halaman
            const select = document.createElement('select');
            select.className = 'px-2 py-1 mx-1 border border-gray-300 rounded';

            for (let i = 1; i <= lastPage; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = `${i}`;
                if (i === currentPage) option.selected = true;
                select.appendChild(option);
            }

            select.addEventListener('change', (e) => {
                const selectedPage = parseInt(e.target.value);
                fetchUser(selectedPage, currentPerPage);
            });

            pagination.appendChild(select);

            // Tombol Next
            if (currentPage < lastPage) {
                const nextLink = document.createElement('a');
                nextLink.href = '#';
                nextLink.textContent = 'Next >';
                nextLink.className = 'px-2 py-1 mx-1 text-[#161D6F] hover:text-blue-500 duration-300';
                nextLink.addEventListener('click', (e) => {
                    e.preventDefault(); // cegah perilaku default
                    fetchUser(currentPage + 1, currentPerPage);
                });
                pagination.appendChild(nextLink);
            }
        }

        document.getElementById('per_page').addEventListener('change', (e) => {
            currentPerPage = parseInt(e.target.value);
            currentPage = 1; // reset ke halaman pertama saat jumlah berubah
            fetchUser(currentPage, currentPerPage);
        });

        // search
        function debounce(func, delay) {
            let timeout;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), delay);
            };
        }

        const debouncedSearch = debounce((e) => {
            const keyword = e.target.value;
            fetchUser(1, currentPerPage, keyword);
        }, 500); // tunggu 500ms

        document.getElementById('search').addEventListener('input', debouncedSearch);


        // Load saat pertama kali
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('per_page');
            currentPerPage = parseInt(select.value);
            fetchUser(currentPageGlobal, currentPerPage);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.deleteUser = async function (id) {
                const token = sessionStorage.getItem('auth_token');

                try {
                    const response = await fetch(`https://backend-alganis-production.up.railway.app/api/pegawai/delete/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        alert('Data berhasil dihapus');
                        this.showModalUser = false;
                        // reload halaman atau ambil ulang data
                        location.reload(); // atau fetchDashboardData()
                    } else {
                        alert('Gagal menghapus: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error(error);
                    alert('Terjadi kesalahan saat menghapus data.');
                }
            }
        });
    </script>
@endsection