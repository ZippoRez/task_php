import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom'; 
import AccountList from './components/AccountList';
import AccountCreate from './components/AccountCreate';
import AccountEdit from './components/AccountEdit'; 
import Trash from './components/Trash';
import CompanyList from './components/CompanyList';
import CompanyCreate from './components/CompanyCreate';
import CompanyEdit from './components/CompanyEdit';
import CompanyDetails from './components/CompanyDetails'; 

const App: React.FC = () => {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<AccountList />} /> 
        <Route path="/create" element={<AccountCreate />} />
        <Route path="/edit/:id" element={<AccountEdit />} />
        <Route path="/trash" element={<Trash />} />

        <Route path="/companies" element={<CompanyList />} /> {/* Список компаний */}
        <Route path="/companies/create" element={<CompanyCreate />} /> {/* Создание компании */}
        <Route path="/companies/edit/:id" element={<CompanyEdit />} /> {/* Редактирование компании */}
        <Route path="/companies/:id" element={<CompanyDetails />} /> {/* Информация о компании */}
      </Routes>
    </BrowserRouter>
  );
};

export default App;