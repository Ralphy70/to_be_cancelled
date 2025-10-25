import React, { useState, useEffect } from 'react';
import { View, StyleSheet, FlatList } from 'react-native';
import { Card, Title, Text } from 'react-native-paper';
import { financeursService } from '../services/api';

export default function FinanceursScreen() {
  const [financeurs, setFinanceurs] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadFinanceurs();
  }, []);

  const loadFinanceurs = async () => {
    try {
      const response = await financeursService.getAll();
      if (response.success) {
        setFinanceurs(response.financeurs);
      }
    } catch (error) {
      console.error('Error loading financeurs:', error);
    } finally {
      setLoading(false);
    }
  };

  const renderFinanceur = ({ item }) => (
    <Card style={styles.card}>
      <Card.Content>
        <Title>{item.nom}</Title>
        <Text style={styles.type}>Type: {item.type}</Text>
        {item.contact_nom && <Text>Contact: {item.contact_nom}</Text>}
        {item.contact_email && <Text>Email: {item.contact_email}</Text>}
        {item.contact_telephone && <Text>Tél: {item.contact_telephone}</Text>}
      </Card.Content>
    </Card>
  );

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <Text>Chargement...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <FlatList
        data={financeurs}
        renderItem={renderFinanceur}
        keyExtractor={(item) => item.id.toString()}
        contentContainerStyle={styles.listContent}
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>Aucun financeur</Text>
          </View>
        }
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f6fa',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  listContent: {
    padding: 10,
  },
  card: {
    marginBottom: 10,
  },
  type: {
    color: '#7f8c8d',
    marginBottom: 5,
  },
  emptyContainer: {
    padding: 20,
    alignItems: 'center',
  },
  emptyText: {
    color: '#95a5a6',
    fontSize: 16,
  },
});
