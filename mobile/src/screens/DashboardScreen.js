import React, { useState, useEffect, useCallback } from 'react';
import { View, StyleSheet, ScrollView, RefreshControl } from 'react-native';
import { Card, Title, Text } from 'react-native-paper';
import { statsService } from '../services/api';
import { formatMontant } from '../utils/helpers';

export default function DashboardScreen() {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadStats = async () => {
    try {
      const response = await statsService.getStats();
      if (response.success) {
        setStats(response.stats);
      }
    } catch (error) {
      console.error('Error loading stats:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    loadStats();
  }, []);

  const onRefresh = useCallback(() => {
    setRefreshing(true);
    loadStats();
  }, []);

  if (loading) {
    return (
      <View style={styles.container}>
        <Text>Chargement...</Text>
      </View>
    );
  }

  return (
    <ScrollView
      style={styles.container}
      refreshControl={
        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
      }
    >
      <View style={styles.header}>
        <Title style={styles.headerTitle}>Tableau de bord</Title>
      </View>

      <View style={styles.statsContainer}>
        <Card style={styles.statCard}>
          <Card.Content>
            <Text style={styles.statValue}>{stats?.total_chantiers || 0}</Text>
            <Text style={styles.statLabel}>Total Chantiers</Text>
          </Card.Content>
        </Card>

        <Card style={styles.statCard}>
          <Card.Content>
            <Text style={styles.statValue}>{stats?.chantiers_en_cours || 0}</Text>
            <Text style={styles.statLabel}>En cours</Text>
          </Card.Content>
        </Card>
      </View>

      <View style={styles.statsContainer}>
        <Card style={styles.statCard}>
          <Card.Content>
            <Text style={styles.statValue}>
              {formatMontant(stats?.budget_total || 0)}
            </Text>
            <Text style={styles.statLabel}>Budget Total</Text>
          </Card.Content>
        </Card>

        <Card style={styles.statCard}>
          <Card.Content>
            <Text style={styles.statValue}>
              {formatMontant(stats?.total_verse || 0)}
            </Text>
            <Text style={styles.statLabel}>Fonds Versés</Text>
          </Card.Content>
        </Card>
      </View>

      <Card style={styles.infoCard}>
        <Card.Content>
          <Title>Bienvenue</Title>
          <Text style={{ marginTop: 10 }}>
            Cette application vous permet de gérer vos chantiers immobiliers,
            leurs budgets et leurs financements depuis votre mobile.
          </Text>
        </Card.Content>
      </Card>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f6fa',
  },
  header: {
    padding: 20,
    backgroundColor: '#2c3e50',
  },
  headerTitle: {
    color: '#fff',
    fontSize: 24,
  },
  statsContainer: {
    flexDirection: 'row',
    padding: 10,
    gap: 10,
  },
  statCard: {
    flex: 1,
    marginHorizontal: 5,
  },
  statValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#2c3e50',
    textAlign: 'center',
  },
  statLabel: {
    fontSize: 12,
    color: '#7f8c8d',
    textAlign: 'center',
    marginTop: 5,
  },
  infoCard: {
    margin: 10,
    marginTop: 20,
  },
});
