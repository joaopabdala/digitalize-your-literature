<header class="bg-gray-800 text-white p-4 shadow-md">
    <div class="container mx-auto flex justify-between items-center">
        <a href="/" class="text-2xl font-bold text-white hover:text-gray-300">
            Digitalize your Literature
        </a>

        <nav>
            <ul class="flex space-x-6">
                <li><a href="/" class="hover:text-gray-300 transition duration-300 ease-in-out">Home</a></li>

                @guest
                    <li><a href="{{ route('login') }}" class="hover:text-gray-300 transition duration-300 ease-in-out">Login</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-gray-300 transition duration-300 ease-in-out">Register</a></li>
                @else
                    <li><a href="{{ route('dashboard') }}" class="hover:text-gray-300 transition duration-300 ease-in-out">Dashboard</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="hover:text-gray-300 transition duration-300 ease-in-out">Logout</button>
                        </form>
                    </li>
                @endguest
            </ul>
        </nav>
    </div>
</header>
