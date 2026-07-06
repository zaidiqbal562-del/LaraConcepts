<nav style="padding:15px; border-bottom:1px solid #ccc; margin-bottom:20px;">

    @auth
        <span>
            Welcome, <strong>{{ auth()->user()->name }}, {{auth()->user()->email}}</strong></br>
            @if(auth()->user()->is_super_user)
                You are Super User
            @endif
        </span>

        <form action="{{ route('logout') }}" method="POST" style="display:inline; margin-left:15px;">
            @csrf
            <button type="submit">
                Logout
            </button>
        </form>
    </br>
        {{-- //search option into project  --}}
        <form action="{{route('projects.search')}}" method="POST" style="display:inline;margin-left-15px;">
            @csrf
            <input type="text" name="Search" placeholder="search project">
            <button type="submit">Search Project</button>
            </form>
        @else
        <a href="{{ route('login') }}">Login</a>
         <!-- Firebase + FCM token saving for authenticated users -->
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-messaging-compat.js"></script>
    <script>
        (async function(){
            try{
                const firebaseConfig = {
                    apiKey: "{{ env('FIREBASE_API_KEY') }}",
                    authDomain: "{{ env('FIREBASE_AUTH_DOMAIN') }}",
                    projectId: "{{ env('FIREBASE_PROJECT_ID') }}",
                    storageBucket: "{{ env('FIREBASE_STORAGE_BUCKET') }}",
                    messagingSenderId: "{{ env('FIREBASE_MESSAGING_SENDER_ID') }}",
                    appId: "{{ env('FIREBASE_APP_ID') }}",
                };

                firebase.initializeApp(firebaseConfig);
                const messaging = firebase.messaging();

                // Register service worker (required for FCM)
                const sw = await navigator.serviceWorker.register('/firebase-messaging-sw.js');

                const vapidKey = "{{ env('FIREBASE_VAPID_KEY') }}";

                // request permission and get token
                const token = await messaging.getToken({vapidKey, serviceWorkerRegistration: sw});
                if(token){
                    // send token to backend
                    await fetch("{{ route('save-fcm-token') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            user_id: {{ auth()->id() }},
                            fcm_token: token
                        })
                    });
                }
            }catch(e){
                // fail silently — token saving is best-effort
                console.error('FCM token save failed', e);
            }
        })();
    </script>
    @endauth

</nav>