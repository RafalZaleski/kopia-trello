import FriendsList from './pages/List.vue'
import FriendForm from './pages/Form.vue'

export default [
    {
        path: '/przyjaciele',
        name: 'friends',
        component: FriendsList, children: [
            {
                path: "/przyjaciele/dodaj",
                name: "friendForm",
                component: FriendForm
            },
        ]
    }
]