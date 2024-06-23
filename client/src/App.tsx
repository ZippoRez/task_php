import React from 'react';
import { BrowserRouter, Routes, Route} from 'react-router-dom';
import {AppBar, Toolbar, Typography, Container } from '@mui/material';
import AccountList from './components/AccountList';
import AccountCreate from './components/AccountCreate';
import AccountEdit from './components/AccountEdit';
import Trash from './components/Trash';

const App: React.FC = () => {
  return (
    <BrowserRouter>
      <AppBar position="static">
        <Toolbar>
          <Typography variant="h6">
            Управление аккаунтами
          </Typography>
        </Toolbar>
      </AppBar>

      <Container maxWidth="xl" style={{ marginTop: '20px' }}>
        <Routes>
          <Route path="/" element={<AccountList />} />
          <Route path="/create" element={<AccountCreate />} />
          <Route path="/edit/:id" element={<AccountEdit />} />
          <Route path="/trash" element={<Trash />} />
        </Routes>
      </Container>
    </BrowserRouter>
  );
};

export default App;