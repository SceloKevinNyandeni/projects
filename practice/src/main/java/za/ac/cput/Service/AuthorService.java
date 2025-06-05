package za.ac.cput.Service;

import org.springframework.stereotype.Service;
import za.ac.cput.Domain.Author;
import za.ac.cput.Repository.AuthorRepository;

import java.util.List;
@Service
public class AuthorService {
    private AuthorRepository authorRepository;

    public AuthorService(AuthorRepository authorRepository) {
        this.authorRepository = authorRepository;
    }
    public Author save(Author author) {
        return authorRepository.save(author);
    }

    public Author readAuthorById(Long authorId) {
        return authorRepository.findById(authorId).orElse(null);
    }
    public Author updateAuthor(Author author) {
        if (authorRepository.existsById(author.getAuthorId())) {
            return authorRepository.save(author);
        }
        return null; // or throw an exception if preferred
    }
    public void deleteAuthor(Long authorId) {
        authorRepository.deleteById(authorId);
    }
    public List<Author> getAllAuthors() {
        return authorRepository.findAll();
    }
}
