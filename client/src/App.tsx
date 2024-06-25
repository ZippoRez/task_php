import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom'; // Для маршрутизации
import { AppBar, Toolbar, Typography, Container } from '@mui/material'; // Компоненты Material-UI
import AccountList from './components/AccountList'; // Компонент списка аккаунтов
import AccountCreate from './components/AccountCreate'; // Компонент создания аккаунта
import AccountEdit from './components/AccountEdit'; // Компонент редактирования аккаунта
import Trash from './components/Trash'; // Компонент корзины

// Главный компонент приложения
const App: React.FC = () => {
  return (
    // BrowserRouter для управления маршрутизацией
    <BrowserRouter>
      {/* AppBar - шапка приложения */}
      <AppBar position="static">
        <Toolbar>
          <Typography variant="h6">
            Управление аккаунтами 
          </Typography>
        </Toolbar>
      </AppBar>

      {/* Контент приложения */}
      <Container maxWidth="xl" style={{ marginTop: '20px' }}> 
        {/* Routes для определения маршрутов */}
        <Routes>
          {/* Маршрут для главной страницы (список аккаунтов) */}
          <Route path="/" element={<AccountList />} /> 
          {/* Маршрут для страницы создания аккаунта */}
          <Route path="/create" element={<AccountCreate />} />
          {/* Маршрут для страницы редактирования аккаунта */}
          <Route path="/edit/:id" element={<AccountEdit />} /> 
          {/* Маршрут для страницы корзины (удаленные аккаунты) */}
          <Route path="/trash" element={<Trash />} />
        </Routes>
      </Container>
    </BrowserRouter>
  );
};

export default App; 