import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  StyleSheet,
  FlatList,
  RefreshControl,
  TouchableOpacity,
} from 'react-native';
import { Card, Title, Text, Chip, FAB } from 'react-native-paper';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import { chantiersService } from '../services/api';
import { formatMontant, translateStatus, getStatusColor } from '../utils/helpers';

export default function ChantiersScreen() {
  const [chantiers, setChantiers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const navigation = useNavigation();

  const loadChantiers = async () => {
    try {
      const response = await chantiersService.getAll();
      if (response.success) {
        setChantiers(response.chantiers);
      }
    } catch (error) {
      console.error('Error loading chantiers:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(
    useCallback(() => {
      loadChantiers();
    }, [])
  );

  const onRefresh = useCallback(() => {
    setRefreshing(true);
    loadChantiers();
  }, []);

  const renderChantier = ({ item }) => (
    <TouchableOpacity
      onPress={() => navigation.navigate('ChantierDetail', { id: item.id })}
    >
      <Card style={styles.card}>
        <Card.Content>
          <View style={styles.cardHeader}>
            <Title style={styles.cardTitle}>{item.nom}</Title>
            <Chip
              style={[
                styles.statusChip,
                { backgroundColor: getStatusColor(item.statut) },
              ]}
              textStyle={{ color: '#fff' }}
            >
              {translateStatus(item.statut)}
            </Chip>
          </View>
          <Text style={styles.cardSubtitle}>{item.adresse}</Text>
          <Text style={styles.cardBudget}>
            Budget: {formatMontant(item.budget_total)}
          </Text>
        </Card.Content>
      </Card>
    </TouchableOpacity>
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
        data={chantiers}
        renderItem={renderChantier}
        keyExtractor={(item) => item.id.toString()}
        contentContainerStyle={styles.listContent}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>Aucun chantier</Text>
          </View>
        }
      />
      <FAB
        style={styles.fab}
        icon="plus"
        onPress={() => navigation.navigate('ChantierForm', { mode: 'create' })}
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
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 5,
  },
  cardTitle: {
    fontSize: 18,
    flex: 1,
  },
  statusChip: {
    height: 24,
  },
  cardSubtitle: {
    color: '#7f8c8d',
    marginBottom: 5,
  },
  cardBudget: {
    fontWeight: 'bold',
    color: '#2c3e50',
  },
  emptyContainer: {
    padding: 20,
    alignItems: 'center',
  },
  emptyText: {
    color: '#95a5a6',
    fontSize: 16,
  },
  fab: {
    position: 'absolute',
    margin: 16,
    right: 0,
    bottom: 0,
    backgroundColor: '#3498db',
  },
});
