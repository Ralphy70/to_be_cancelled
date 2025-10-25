import { createContext } from 'react';

export const AuthContext = createContext({
  signIn: () => {},
  signOut: () => {},
  user: null,
  token: null,
});
