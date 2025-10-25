import React, { useContext } from 'react';
import { View, StyleSheet, Alert } from 'react-native';
import { Card, Title, Text, Button, Divider } from 'react-native-paper';
import { AuthContext } from '../context/AuthContext';

export default function ProfileScreen() {
  const { user, signOut } = useContext(AuthContext);

  const handleLogout = () => {
    Alert.alert(
      'Déconnexion',
      'Êtes-vous sûr de vouloir vous déconnecter ?',
      [
        { text: 'Annuler', style: 'cancel' },
        { text: 'Déconnexion', onPress: () => signOut(), style: 'destructive' },
      ]
    );
  };

  return (
    <View style={styles.container}>
      <Card style={styles.card}>
        <Card.Content>
          <Title>Profil</Title>
          <Divider style={{ marginVertical: 10 }} />
          <Text style={styles.label}>Nom d'utilisateur</Text>
          <Text style={styles.value}>{user?.username}</Text>
          <Text style={styles.label}>Email</Text>
          <Text style={styles.value}>{user?.email}</Text>
          <Text style={styles.label}>Rôle</Text>
          <Text style={styles.value}>
            {user?.role === 'admin' ? 'Administrateur' : 'Utilisateur'}
          </Text>
        </Card.Content>
      </Card>

      <Card style={styles.card}>
        <Card.Content>
          <Title>Application</Title>
          <Text style={{ marginTop: 10 }}>
            Gestion de Chantiers Mobile{'\n'}
            Version 1.0.0
          </Text>
        </Card.Content>
      </Card>

      <Button
        mode="contained"
        onPress={handleLogout}
        style={styles.logoutButton}
        buttonColor="#e74c3c"
      >
        Déconnexion
      </Button>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f6fa',
    padding: 10,
  },
  card: {
    marginBottom: 15,
  },
  label: {
    fontSize: 12,
    color: '#7f8c8d',
    marginTop: 10,
  },
  value: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#2c3e50',
  },
  logoutButton: {
    marginTop: 20,
  },
});
