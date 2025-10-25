import React, { useState, useEffect } from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createStackNavigator } from '@react-navigation/stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Provider as PaperProvider } from 'react-native-paper';
import Icon from 'react-native-vector-icons/MaterialCommunityIcons';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Context
import { AuthContext } from './src/context/AuthContext';

// Screens
import LoginScreen from './src/screens/LoginScreen';
import DashboardScreen from './src/screens/DashboardScreen';
import ChantiersScreen from './src/screens/ChantiersScreen';
import ChantierDetailScreen from './src/screens/ChantierDetailScreen';
import ChantierFormScreen from './src/screens/ChantierFormScreen';
import PosteDetailScreen from './src/screens/PosteDetailScreen';
import PosteFormScreen from './src/screens/PosteFormScreen';
import DepenseFormScreen from './src/screens/DepenseFormScreen';
import FinanceursScreen from './src/screens/FinanceursScreen';
import FinancementDetailScreen from './src/screens/FinancementDetailScreen';
import ProfileScreen from './src/screens/ProfileScreen';

const Stack = createStackNavigator();
const Tab = createBottomTabNavigator();

function MainTabs() {
  return (
    <Tab.Navigator
      screenOptions={{
        tabBarActiveTintColor: '#2c3e50',
        tabBarInactiveTintColor: '#95a5a6',
      }}
    >
      <Tab.Screen
        name="Dashboard"
        component={DashboardScreen}
        options={{
          title: 'Tableau de bord',
          tabBarIcon: ({ color, size }) => (
            <Icon name="view-dashboard" color={color} size={size} />
          ),
        }}
      />
      <Tab.Screen
        name="Chantiers"
        component={ChantiersScreen}
        options={{
          title: 'Chantiers',
          tabBarIcon: ({ color, size }) => (
            <Icon name="office-building" color={color} size={size} />
          ),
        }}
      />
      <Tab.Screen
        name="Financeurs"
        component={FinanceursScreen}
        options={{
          title: 'Financeurs',
          tabBarIcon: ({ color, size }) => (
            <Icon name="bank" color={color} size={size} />
          ),
        }}
      />
      <Tab.Screen
        name="Profile"
        component={ProfileScreen}
        options={{
          title: 'Profil',
          tabBarIcon: ({ color, size }) => (
            <Icon name="account" color={color} size={size} />
          ),
        }}
      />
    </Tab.Navigator>
  );
}

export default function App() {
  const [userToken, setUserToken] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [user, setUser] = useState(null);

  useEffect(() => {
    // Vérifier si un token existe au démarrage
    const bootstrapAsync = async () => {
      try {
        const token = await AsyncStorage.getItem('userToken');
        const userData = await AsyncStorage.getItem('userData');

        if (token && userData) {
          setUserToken(token);
          setUser(JSON.parse(userData));
        }
      } catch (e) {
        console.error('Error loading auth data:', e);
      }
      setIsLoading(false);
    };

    bootstrapAsync();
  }, []);

  const authContext = React.useMemo(
    () => ({
      signIn: async (token, userData) => {
        try {
          await AsyncStorage.setItem('userToken', token);
          await AsyncStorage.setItem('userData', JSON.stringify(userData));
          setUserToken(token);
          setUser(userData);
        } catch (e) {
          console.error('Error saving auth data:', e);
        }
      },
      signOut: async () => {
        try {
          await AsyncStorage.removeItem('userToken');
          await AsyncStorage.removeItem('userData');
          setUserToken(null);
          setUser(null);
        } catch (e) {
          console.error('Error removing auth data:', e);
        }
      },
      user,
      token: userToken,
    }),
    [userToken, user]
  );

  if (isLoading) {
    return null; // Ou un écran de chargement
  }

  return (
    <AuthContext.Provider value={authContext}>
      <PaperProvider>
        <NavigationContainer>
          <Stack.Navigator>
            {userToken == null ? (
              <Stack.Screen
                name="Login"
                component={LoginScreen}
                options={{ headerShown: false }}
              />
            ) : (
              <>
                <Stack.Screen
                  name="Main"
                  component={MainTabs}
                  options={{ headerShown: false }}
                />
                <Stack.Screen
                  name="ChantierDetail"
                  component={ChantierDetailScreen}
                  options={{ title: 'Détails du chantier' }}
                />
                <Stack.Screen
                  name="ChantierForm"
                  component={ChantierFormScreen}
                  options={{ title: 'Chantier' }}
                />
                <Stack.Screen
                  name="PosteDetail"
                  component={PosteDetailScreen}
                  options={{ title: 'Détails du poste' }}
                />
                <Stack.Screen
                  name="PosteForm"
                  component={PosteFormScreen}
                  options={{ title: 'Poste budgétaire' }}
                />
                <Stack.Screen
                  name="DepenseForm"
                  component={DepenseFormScreen}
                  options={{ title: 'Dépense' }}
                />
                <Stack.Screen
                  name="FinancementDetail"
                  component={FinancementDetailScreen}
                  options={{ title: 'Détails du financement' }}
                />
              </>
            )}
          </Stack.Navigator>
        </NavigationContainer>
      </PaperProvider>
    </AuthContext.Provider>
  );
}
